<?php

namespace App;

class Tahvel
{


    /**
     * Expects an array of subjects with assignments that contain grades
     * Adds subjects and assignments to Kriit if they don't exist
     * Compares the grades in Kriit with the grades in Tahvel, and returns the differences. Also inserts the missing subjects, groups, teachers, assignments and students.
     * Reports also the differences between instructions or due dates.
     * @param $journals array of Tahvel journals (subjects in Tahvel) with assignments that contain grades
     * @return array of subjects with assignments that contain grades that are different from the ones in Kriit
     */
    static function sync(array $journals): array {
        $response = [];

        // Get a comma-separated list of all journalIds from tahvelSubjects
        $journalIdsArray = array_map(function($subject) {
            return $subject['subjectExternalId'];
        }, $journals);
        $journalIdsString = implode(',', $journalIdsArray);

        if(empty($journalIdsString)) {
            return $response;
        }

        $rows = Db::getAll("
            SELECT subjectName,
                subjectExternalId,
                groupName,
                assignmentExternalId,
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
            WHERE subjectExternalId IN ($journalIdsString)");

        foreach ($rows as $row) {
            $subjects[$row['subjectExternalId']]['subjectName'] = $row['subjectName'];
            $subjects[$row['subjectExternalId']]['subjectExternalId'] = $row['subjectExternalId'];
            $subjects[$row['subjectExternalId']]['groupName'] = $row['groupName'];
            $subjects[$row['subjectExternalId']]['teacherPersonalCode'] = $row['teacherPersonalCode'];
            $subjects[$row['subjectExternalId']]['teacherName'] = $row['teacherName'];
            $subjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['assignmentExternalId'] = $row['assignmentExternalId'];
            $subjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['assignmentName'] = $row['assignmentName'];
            $subjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['assignmentInstructions'] = $row['assignmentInstructions'];
            $subjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['assignmentDueAt'] = $row['assignmentDueAt'];
            $subjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['results'][$row['studentPersonalCode']]['grade'] = $row['userGrade'];
            $subjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['results'][$row['studentPersonalCode']]['studentPersonalCode'] = $row['studentPersonalCode'];
            $subjects[$row['subjectExternalId']]['assignments'][$row['assignmentExternalId']]['results'][$row['studentPersonalCode']]['studentName'] = $row['studentName'];
        }


        foreach ($journals as $journal) {

            // Verify that the teacher is in Kriit
            // TODO: If this teacher has been already validated in some previous iteration, we should skip this step
            $kriitTeacher = Db::getFirst("SELECT * FROM users WHERE userPersonalCode = '{$journal['teacherPersonalCode']}'");

            if (empty($kriitTeacher)) {
                // Log this abnormal situation
                error_log("Kriit does not have teacher with personalCode {$journal['teacherPersonalCode']}");

                // Insert the teacher in Kriit
                Db::insert('users', [
                    'userPersonalCode' => $journal['teacherPersonalCode'],
                    'userName' => $journal['teacherName'],
                    'userIsTeacher' => 1
                ]);
            }

            // Verify that the group is in Kriit
            $kriitGroup = Db::getFirst("SELECT * FROM `groups` WHERE groupName = '{$journal['groupName']}'");

            if (empty($kriitGroup)) {

                // Log this abnormal situation
                error_log("Kriit does not have group with name {$journal['groupName']}");

                // Insert the group in Kriit
                Db::insert('groups', ['groupName' => $journal['groupName']]);

            }

            // Verify that the subject is in Kriit
            $subject = array_filter($subjects, function ($kriitSubject) use ($journal) {
                return $kriitSubject['subjectExternalId'] == $journal['subjectExternalId'];
            });

            // If we didn't find a match, we'll return all the assignments from Tahvel with no grades
            if (empty($subject)) {

                // Log this abnormal situation
                error_log("No match for journalId {$journal['subjectExternalId']}");

                $newSubjectId = Db::insert('subjects', [
                    'subjectName' => $journal['subjectName'],
                    'tahvelSubjectId' => $journal['subjectExternalId'],
                    'groupId' => $kriitGroup['userGroupId'],
                    'teacherId' => $kriitTeacher['userId']
                ]);

                // Iterate over the assignments and insert them in Kriit
                foreach ($journal['assignments'] as $tahvelAssignment) {
                    Db::insert('assignments', [
                        'subjectId' => $newSubjectId,
                        'assignmentName' => $tahvelAssignment['assignmentName'],
                        'assignmentExternalId' => $tahvelAssignment['assignmentExternalId'],
                        'assignmentDueAt' => $tahvelAssignment['assignmentDueAt'],
                        'assignmentInstructions' => $tahvelAssignment['assignmentInstructions']
                    ]);
                }

                $response[$journal['subjectExternalId']] = [
                    'subjectName' => $journal['subjectName'],
                    'subjectExternalId' => $journal['subjectExternalId'],
                    'groupName' => $journal['groupName'],
                    'teacherPersonalCode' => $journal['teacherPersonalCode'],
                    'teacherName' => $journal['teacherName']
                ];

                // Add assignments to the response
                foreach ($journal['assignments'] as $tahvelAssignment) {
                    $response[$journal['subjectExternalId']]['assignments'][$tahvelAssignment['assignmentExternalId']] = [
                        'assignmentExternalId' => $tahvelAssignment['assignmentExternalId'],
                        'assignmentName' => $tahvelAssignment['assignmentName'],
                        'assignmentInstructions' => $tahvelAssignment['assignmentInstructions'],
                        'assignmentDueAt' => $tahvelAssignment['assignmentDueAt']
                    ];

                    // Iterate over the students that Tahvel sent and add them to the response with grades set to null
                    foreach ($tahvelAssignment['results'] as $tahvelStudent) {

                        // Only add students that have a grade in Tahvel (if Tahvel doesn't have a grade, it's the same in Kriit and thus there is no difference)
                        if (!empty($tahvelStudent['grade'])) {
                            $response[$journal['subjectExternalId']]['assignments'][$tahvelAssignment['assignmentExternalId']]['results'][$tahvelStudent['studentPersonalCode']] = [
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
            foreach ($journal['assignments'] as $tahvelAssignment) {

                // Set all grades to null for this assignment in the response if the assignment is not found in Kriit
                if (!isset($subject['assignments'][$tahvelAssignment['assignmentExternalId']])) {

                    // Log this abnormal situation
                    error_log("Kriit does not have assignment with assignmentExternalId {$tahvelAssignment['assignmentExternalId']}");

                    // TODO: Make sure all students are in Kriit (but cache the result and don't check next time)

                    // Insert the assignment in Kriit
                    Db::insert('assignments', [
                        'subjectId' => $subject['subjectId'],
                        'assignmentName' => $tahvelAssignment['assignmentName'],
                        'assignmentExternalId' => $tahvelAssignment['assignmentExternalId'],
                        'assignmentDueAt' => $tahvelAssignment['assignmentDueAt'],
                        'assignmentInstructions' => $tahvelAssignment['assignmentInstructions']
                    ]);

                    // Iterate over tahvel students and if they have a grade in tahvel, insert them to results:
                    foreach ($tahvelAssignment['results'] as $tahvelStudent) {

                        // Only add students that have a grade in Tahvel (if Tahvel doesn't have a grade, it's the same in Kriit and thus there is no difference)
                        if (!empty($tahvelStudent['grade'])) {
                            Db::insert('userAssignments', [
                                'assignmentId' => $subject['assignments'][$tahvelAssignment['assignmentExternalId']]['assignmentId'],
                                'userId' => Db::getOne("SELECT userId FROM users WHERE userPersonalCode = '{$tahvelStudent['studentPersonalCode']}'"),
                                'orderedTrayCount' => null,
                                'comments' => null
                            ]);
                        }

                    }

                    continue;
                }

                // Create a diff between the assignments from Tahvel and the assignments in Kriit
                $diff = array_diff_assoc($tahvelAssignment['results'], $subject['assignments'][$tahvelAssignment['assignmentExternalId']]['results']);

                // If there are any differences, add them to the response
                if (!empty($diff)) {

                    $response[$journal['subjectExternalId']]['subjectName'] = $subject['subjectName'];
                    $response[$journal['subjectExternalId']]['subjectExternalId'] = $subject['subjectExternalId'];
                    $response[$journal['subjectExternalId']]['groupName'] = $subject['groupName'];
                    $response[$journal['subjectExternalId']]['teacherPersonalCode'] = $subject['teacherPersonalCode'];
                    $response[$journal['subjectExternalId']]['teacherName'] = $subject['teacherName'];
                    $response[$journal['subjectExternalId']]['assignments'][$tahvelAssignment['assignmentExternalId']]['results'] = $diff;
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