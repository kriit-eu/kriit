<?php

namespace App;

class Sync
{


    /**
     * Compares the grades in Kriit with the grades in Tahvel, and returns the differences. Also inserts the missing subjects, groups, teachers, assignments and students.
     * Reports also the differences between instructions or due dates.
     * @param $tahvelSubjects
     * @return array of subjects with assignments that contain grades that are different from the ones in Kriit
     */
    static function getUnsyncedGrades($tahvelSubjects): array {
        $response = [];

        // Get a comma-separated list of all subjectExternalIds from tahvelSubjects
        $subjectExternalIdsArray = array_map(function($subject) {
            return $subject['subjectExternalId'];
        }, $tahvelSubjects);
        $subjectExternalIdsString = implode(',', $subjectExternalIdsArray);

        if(empty($subjectExternalIdsString)) {
            return $response;
        }

        $rows = Db::getAll("
            SELECT subjectName,
                tahvelSubjectId AS subjectExternalId,
                groupName,
                tahvelJournalEntryId AS assignmentExternalId,
                assignmentName,
                assignmentInstructions,
                assignmentDueAt,
                userGrade,
                student.userPersonalCode AS studentPersonalCode,
                student.userName AS studentName,
                teacher.userPersonalCode AS teacherPersonalCode,
                teacher.userName AS teacherName
            FROM subjects
                JOIN `groups` USING (groupId)
                JOIN assignments USING (subjectId)
                LEFT JOIN userAssignments USING (assignmentId)
                JOIN users student USING (userId)
                JOIN users teacher ON teacher.userId = subjects.teacherId
            WHERE tahvelSubjectId IN ($subjectExternalIdsString)");

        foreach ($rows as $row) {
            $kriitSubjects[$row['subjectExternalId']]['subjectName'] = $row['subjectName'];
            $kriitSubjects[$row['subjectExternalId']]['subjectExternalId'] = $row['subjectExternalId'];
            $kriitSubjects[$row['subjectExternalId']]['groupName'] = $row['groupName'];
            $kriitSubjects[$row['subjectExternalId']]['teacherPersonalCode'] = $row['teacherPersonalCode'];
            $kriitSubjects[$row['subjectExternalId']]['teacherName'] = $row['teacherName'];
            $kriitSubjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['assignmentExternalId'] = $row['assignmentExternalId'];
            $kriitSubjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['assignmentName'] = $row['assignmentName'];
            $kriitSubjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['assignmentInstructions'] = $row['assignmentInstructions'];
            $kriitSubjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['assignmentDueAt'] = $row['assignmentDueAt'];
            $kriitSubjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['results'][$row['studentPersonalCode']]['grade'] = $row['userGrade'];
            $kriitSubjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['results'][$row['studentPersonalCode']]['studentPersonalCode'] = $row['studentPersonalCode'];
            $kriitSubjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['results'][$row['studentPersonalCode']]['studentName'] = $row['studentName'];
        }


        foreach ($tahvelSubjects as $tahvelSubject) {

            // Verify that the teacher is in Kriit
            // TODO: If this teacher has been already validated in some previous iteration, we should skip this step
            $kriitTeacher = Db::getFirst("SELECT * FROM users WHERE userPersonalCode = '{$tahvelSubject['teacherPersonalCode']}'");

            if (empty($kriitTeacher)) {
                // Log this abnormal situation
                error_log("Kriit does not have teacher with personalCode {$tahvelSubject['teacherPersonalCode']}");

                // Insert the teacher in Kriit
                Db::insert('users', [
                    'userPersonalCode' => $tahvelSubject['teacherPersonalCode'],
                    'userName' => $tahvelSubject['teacherName'],
                    'userIsTeacher' => 1
                ]);
            }

            // Verify that the group is in Kriit
            $kriitGroup = Db::getFirst("SELECT * FROM user_groups WHERE userGroupName = '{$tahvelSubject['groupName']}'");

            if (empty($kriitGroup)) {

                // Log this abnormal situation
                error_log("Kriit does not have group with name {$tahvelSubject['groupName']}");

                // Insert the group in Kriit
                Db::insert('groups', ['groupName' => $tahvelSubject['groupName']]);

            }

            // Verify that the subject is in Kriit
            $kriitSubject = array_filter($kriitSubjects, function ($kriitSubject) use ($tahvelSubject) {
                return $kriitSubject['subjectExternalId'] == $tahvelSubject['subjectExternalId'];
            });

            // If we didn't find a match, we'll return all the assignments from Tahvel with no grades
            if (empty($kriitSubject)) {

                // Log this abnormal situation
                error_log("No match for subjectExternalId {$tahvelSubject['subjectExternalId']}");

                $newSubjectId = Db::insert('subjects', [
                    'subjectName' => $tahvelSubject['subjectName'],
                    'tahvelSubjectId' => $tahvelSubject['subjectExternalId'],
                    'groupId' => $kriitGroup['userGroupId'],
                    'teacherId' => $kriitTeacher['userId']
                ]);

                // Iterate over the assignments and insert them in Kriit
                foreach ($tahvelSubject['assignments'] as $tahvelAssignment) {
                    Db::insert('assignments', [
                        'subjectId' => $newSubjectId,
                        'assignmentName' => $tahvelAssignment['assignmentName'],
                        'tahvelJournalEntryId' => $tahvelAssignment['assignmentExternalId'],
                        'assignmentDueAt' => $tahvelAssignment['assignmentDueAt'],
                        'assignmentInstructions' => $tahvelAssignment['assignmentInstructions']
                    ]);
                }

                $response[$tahvelSubject['subjectExternalId']] = [
                    'subjectName' => $tahvelSubject['subjectName'],
                    'subjectExternalId' => $tahvelSubject['subjectExternalId'],
                    'groupName' => $tahvelSubject['groupName'],
                    'teacherPersonalCode' => $tahvelSubject['teacherPersonalCode'],
                    'teacherName' => $tahvelSubject['teacherName']
                ];

                // Add assignments to the response
                foreach ($tahvelSubject['assignments'] as $tahvelAssignment) {
                    $response[$tahvelSubject['subjectExternalId']]['assignments'][$tahvelAssignment['assignmentExternalId']] = [
                        'assignmentExternalId' => $tahvelAssignment['assignmentExternalId'],
                        'assignmentName' => $tahvelAssignment['assignmentName'],
                        'assignmentInstructions' => $tahvelAssignment['assignmentInstructions'],
                        'assignmentDueAt' => $tahvelAssignment['assignmentDueAt']
                    ];

                    // Iterate over the students that Tahvel sent and add them to the response with grades set to null
                    foreach ($tahvelAssignment['results'] as $tahvelStudent) {

                        // Only add students that have a grade in Tahvel (if Tahvel doesn't have a grade, it's the same in Kriit and thus there is no difference)
                        if (!empty($tahvelStudent['grade'])) {
                            $response[$tahvelSubject['subjectExternalId']]['assignments'][$tahvelAssignment['assignmentExternalId']]['results'][$tahvelStudent['studentPersonalCode']] = [
                                'grade' => null,
                                'studentPersonalCode' => $tahvelStudent['studentPersonalCode'],
                                'studentName' => $tahvelStudent['studentName']
                            ];
                        }

                    }

                }

                // Skip the rest of the iteration because we already added the subject to the response
                continue;

            }

            // Compare the assignments
            foreach ($tahvelSubject['assignments'] as $tahvelAssignment) {

                // Set all grades to null for this assignment in the response if the assignment is not found in Kriit
                if (!isset($kriitSubject['assignments'][$tahvelAssignment['assignmentExternalId']])) {

                    // Log this abnormal situation
                    error_log("Kriit does not have assignment with assignmentExternalId {$tahvelAssignment['assignmentExternalId']}");

                    // TODO: Make sure all students are in Kriit (but cache the result and don't check next time)

                    // Insert the assignment in Kriit
                    Db::insert('assignments', [
                        'subjectId' => $kriitSubject['subjectId'],
                        'assignmentName' => $tahvelAssignment['assignmentName'],
                        'tahvelJournalEntryId' => $tahvelAssignment['assignmentExternalId'],
                        'assignmentDueAt' => $tahvelAssignment['assignmentDueAt'],
                        'assignmentInstructions' => $tahvelAssignment['assignmentInstructions']
                    ]);

                    // Iterate over tahvel students and if they have a grade in tahvel, insert them to results:
                    foreach ($tahvelAssignment['results'] as $tahvelStudent) {

                        // Only add students that have a grade in Tahvel (if Tahvel doesn't have a grade, it's the same in Kriit and thus there is no difference)
                        if (!empty($tahvelStudent['grade'])) {
                            Db::insert('userAssignments', [
                                'assignmentId' => $kriitSubject['assignments'][$tahvelAssignment['assignmentExternalId']]['assignmentId'],
                                'userId' => Db::getOne("SELECT userId FROM users WHERE userPersonalCode = '{$tahvelStudent['studentPersonalCode']}'"),
                                'orderedTrayCount' => null,
                                'comments' => null
                            ]);
                        }

                    }

                    continue;
                }

                // Create a diff between the assignments from Tahvel and the assignments in Kriit
                $diff = array_diff_assoc($tahvelAssignment['results'], $kriitSubject['assignments'][$tahvelAssignment['assignmentExternalId']]['results']);

                // If there are any differences, add them to the response
                if (!empty($diff)) {

                    $response[$tahvelSubject['subjectExternalId']]['subjectName'] = $kriitSubject['subjectName'];
                    $response[$tahvelSubject['subjectExternalId']]['subjectExternalId'] = $kriitSubject['subjectExternalId'];
                    $response[$tahvelSubject['subjectExternalId']]['groupName'] = $kriitSubject['groupName'];
                    $response[$tahvelSubject['subjectExternalId']]['teacherPersonalCode'] = $kriitSubject['teacherPersonalCode'];
                    $response[$tahvelSubject['subjectExternalId']]['teacherName'] = $kriitSubject['teacherName'];
                    $response[$tahvelSubject['subjectExternalId']]['assignments'][$tahvelAssignment['assignmentExternalId']]['results'] = $diff;
                }

            }

        }
        return $response;
    }
}
/*
 [
  {
    "subjectName": "Andmebaaside alused",
    "subjectExternalId": 348991,
    "groupName": "TAK24",
    "teacherPersonalCode": "38010050352",
    "teacherName": "Henno Täht",
    "assignments": [
      {
        "assignmentExternalId": 3319949,
        "assignmentName": "",
        "assignmentInstructions": "",
        "assignmentDueAt": null,
        "results": [
          {
            "grade": "A",
            "studentPersonalCode": "49910074220",
            "studentName": "Brigita Kasemets"
          }

        ]
      }
    ]
  }
]
 */