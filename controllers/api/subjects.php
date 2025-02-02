<?php namespace App\api;

use App\Controller;
use App\Db;
use App\Activity;

class subjects extends Controller
{
    function subjectIsSynchronized(): void
    {
        $tahvelSubjectId = $_POST['tahvelSubjectId'];

        $existingSubject = Db::getFirst("SELECT * FROM subjects WHERE tahvelSubjectId = ?", [$tahvelSubjectId]);

        if ($existingSubject) {
            Db::update('subjects', [
                'isSynchronized' => 1
            ], 'tahvelSubjectId = ?', [$tahvelSubjectId]);
            stop(200, 'Subject is synchronized');
        } else {
            stop(400, 'Subject not found');
        }
    }

    function subjectsSynchronizeData(): void
    {
        // Get IDs from POST data and convert to integers
        $subjectIds = array_map('intval', $_POST['tahvelSubjectIds'] ?? []);

        // If no IDs provided, return empty array
        if (!$subjectIds) {
            stop(200, []);
        }

        // Create placeholders for parameterized query
        $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));

        // SQL query to fetch subjects
        // Execute query with IDs as parameters
        $subjects = Db::getAll("
            SELECT tahvelSubjectId, isSynchronized, subjectName, groupName
            FROM subjects
            JOIN `groups` USING (groupId)
            WHERE tahvelSubjectId IN ($placeholders)
        ", $subjectIds);

        // Map subjects by their IDs for easy access
        $subjectsById = array_column($subjects, null, 'tahvelSubjectId');

        // Prepare the data to return
        $data = [];
        foreach ($subjectIds as $id) {
            $subject = $subjectsById[$id] ?? [];
            $data[] = [
                'tahvelSubjectId' => $id,
                'subjectName'     => $subject['subjectName'] ?? '',
                'groupName'       => $subject['groupName'] ?? '',
                'isSynchronized'  => !empty($subject['isSynchronized']),
            ];
        }

        // Return the data with HTTP status 200
        stop(200, $data);
    }

    /**
     * Get unsynced grades for all students in all subjects
     * @used-by Õpetaja Assistent (TahvelJournalList.addUnsynchronizedBanner)
     */
    function getUnsyncedGrades(): void
    {
        // Get the JSON input
        $input = file_get_contents('php://input');
        $subjects = json_decode($input, true);

        // Prepare response array
        $response = [];

        foreach ($subjects as $subjectData) {
            $subjectName = $subjectData['subjectName'];
            $subjectExternalId = $subjectData['subjectExternalId'];
            $groupName = $subjectData['groupName'];
            $teacherPersonalCode = $subjectData['teacherPersonalCode'];
            $teacherName = $subjectData['teacherName'];

            // Find or create teacher
            $teacher = Db::getFirst("SELECT userId FROM users WHERE userPersonalCode = ?", [$teacherPersonalCode]);
            if (!$teacher) {
                $teacherId = Db::insert('users', [
                    'userName' => $teacherName,
                    'userPersonalCode' => $teacherPersonalCode,
                    'userIsTeacher' => 1
                ]);
                Activity::create(ACTIVITY_ADD_USER, $this->auth->userId, $teacherId, ['teacherId' => $teacherId, 'teacherName' => $teacherName]);
            } else {
                $teacherId = $teacher['userId'];
            }

            // Find or create group
            $group = Db::getFirst("SELECT groupId FROM `groups` WHERE groupName = ?", [$groupName]);
            if (!$group) {
                $groupId = Db::insert('groups', [
                    'groupName' => $groupName
                ]);
                Activity::create(ACTIVITY_CREATE_GROUP, $this->auth->userId, $groupId, ['groupId' => $groupId, 'groupName' => $groupName]);
            } else {
                $groupId = $group['groupId'];
            }

            // Find or create subject
            $subject = Db::getFirst("SELECT * FROM subjects WHERE tahvelSubjectId = ?", [$subjectExternalId]);
            if (!$subject) {
                $subjectId = Db::insert('subjects', [
                    'subjectName' => $subjectName,
                    'tahvelSubjectId' => $subjectExternalId,
                    'groupId' => $groupId,
                    'teacherId' => $teacherId,
                    'isSynchronized' => 1
                ]);
                Activity::create(ACTIVITY_CREATE_SUBJECT, $this->auth->userId, $subjectId, ['subjectId' => $subjectId, 'subjectName' => $subjectName, 'groupId' => $groupId, 'teacherId' => $teacherId]);
            } else {
                $subjectId = $subject['subjectId'];
            }

            // Process assignments
            $subjectResponse = [];
            foreach ($subjectData['assignments'] as $assignmentData) {
                $assignmentExternalId = $assignmentData['assignmentExternalId'];
                $assignmentName = $assignmentData['assignmentName'];
                $assignmentInstructions = $assignmentData['assignmentInstructions'];
                $assignmentDueAt = $assignmentData['assignmentDueAt'];

                // Find or create assignment
                $assignment = Db::getFirst("SELECT * FROM assignments WHERE tahvelJournalEntryId = ?", [$assignmentExternalId]);
                if (!$assignment) {
                    $assignmentId = Db::insert('assignments', [
                        'assignmentName' => $assignmentName,
                        'assignmentInstructions' => $assignmentInstructions,
                        'subjectId' => $subjectId,
                        'tahvelJournalEntryId' => $assignmentExternalId,
                        'assignmentDueAt' => date('Y-m-d', strtotime($assignmentDueAt))
                    ]);
                    Activity::create(ACTIVITY_CREATE_ASSIGNMENT, $this->auth->userId, $assignmentId, ['assignmentId' => $assignmentId, 'assignmentName' => $assignmentName, 'subjectId' => $subjectId]);
                } else {
                    $assignmentId = $assignment['assignmentId'];
                }

                // Process student results
                $assignmentResponse = [];
                $hasGradeDifferences = false;

                foreach ($assignmentData['results'] as $result) {
                    $studentPersonalCode = $result['studentPersonalCode'];
                    $studentName = $result['studentName'];
                    $grade = $result['grade'];

                    // Find or create student
                    $student = Db::getFirst("SELECT userId FROM users WHERE userPersonalCode = ?", [$studentPersonalCode]);
                    if (!$student) {
                        $studentId = Db::insert('users', [
                            'userName' => $studentName,
                            'userPersonalCode' => $studentPersonalCode,
                            'userIsTeacher' => 0
                        ]);
                        Activity::create(ACTIVITY_ADD_USER, $this->auth->userId, $studentId, ['studentId' => $studentId, 'studentName' => $studentName]);
                    } else {
                        $studentId = $student['userId'];
                    }

                    // Get existing grade from userAssignments
                    $existingAssignment = Db::getFirst("SELECT userGrade FROM userAssignments WHERE userId = ? AND assignmentId = ?", 
                        [$studentId, $assignmentId]);

                    // Only add to response if:
                    // 1. Tahvel has a grade AND
                    // 2. Grade exists in Kriit AND is different from Tahvel
                    if ($grade !== null && $existingAssignment && $existingAssignment['userGrade'] !== $grade) {
                        // Keep the exact same structure as received from Tahvel
                        $assignmentResponse[] = [
                            'studentPersonalCode' => $studentPersonalCode,
                            'studentName' => $studentName,
                            'grade' => $existingAssignment['userGrade']
                        ];
                        $hasGradeDifferences = true;
                    }

                    $now = date('Y-m-d H:i:s');
                }

                // Only add assignment to response if there were different grades or instructions
                if ($hasGradeDifferences || ($assignment && $assignment['assignmentInstructions'] !== $assignmentInstructions)) {
                    $subjectResponse[] = [
                        'assignmentExternalId' => (int)$assignmentExternalId,
                        'assignmentName' => $assignmentName,
                        'assignmentInstructions' => $assignment ? $assignment['assignmentInstructions'] : '',
                        'assignmentDueAt' => $assignmentDueAt,
                        'results' => $assignmentResponse
                    ];
                }
            }

            // Only add subject to response if there were different assignments/grades
            if (!empty($subjectResponse)) {
                $response[] = [
                    'subjectName' => $subjectName,
                    'subjectExternalId' => (int)$subjectExternalId,
                    'groupName' => $groupName,
                    'teacherPersonalCode' => $teacherPersonalCode,
                    'teacherName' => $teacherName,
                    'assignments' => $subjectResponse
                ];
            }
        }

        stop(200, $response);
    }
}
