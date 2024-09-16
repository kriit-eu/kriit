<?php namespace App\api;

use App\Activity;
use App\Controller;
use App\Db;

// add /api/groups to the URL

class assignments extends Controller
{

    function add()
    {
        // Check that either subjectId or subjectName is provided but not both
        if (empty($_POST['subjectId']) && empty($_POST['subjectName'])) {
            stop(400, 'subjectId or subjectName is required');
        }

        if (!empty($_POST['subjectId']) && !empty($_POST['subjectName'])) {
            stop(400, 'subjectId or subjectName is required but not both');
        }

        // Check that either groupId or groupName is provided but not both
        if (empty($_POST['groupId']) && empty($_POST['groupName'])) {
            stop(400, 'groupId or groupName is required');
        }

        if (!empty($_POST['groupId']) && !empty($_POST['groupName'])) {
            stop(400, 'groupId or groupName is required but not both');
        }

        // Check if tahvelSubjectId is provided
        if (empty($_POST['tahvelSubjectId'])) {
            stop(400, 'tahvelSubjectId is required');
        }

        // Check if tahvelJournalEntryId is provided
        if (empty($_POST['tahvelJournalEntryId'])) {
            stop(400, 'tahvelJournalEntryId is required');
        }

        // if assignmentDueAt is provided, check if it is a valid date
        if (!empty($_POST['assignmentDueAt'])) {
            $dateParts = explode('-', $_POST['assignmentDueAt']);
            if (count($dateParts) !== 3 || !checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
                stop(400, 'Invalid assignmentDueAt');
            }
        }

        // Construct parameterized query to check if group exists based on either groupId or groupName which ever is provided
        $field = !empty($_POST['groupId']) ? 'groupId' : 'groupName';
        $groupId = Db::getOne("SELECT groupId FROM groups WHERE $field = ?", [$_POST[$field]]);

        if (!$groupId) {
            if ($field === 'groupId') {
                stop(400,"Invalid groupId provided");
            } else {
                $groupId = Db::insert('groups', ['groupName' => $_POST['groupName']]);
                Activity::create(ACTIVITY_CREATE_GROUP, $this->auth->userId, $groupId);
            }
        }

        // Construct parameterized query to check if subject exists based on either subjectId or subjectName which ever is provided
        $field = !empty($_POST['subjectId']) ? 'subjectId' : 'subjectName';
        $subjectId = Db::getOne("SELECT subjectId FROM subjects WHERE $field = ?", [$_POST[$field]]);

        if (!$subjectId) {
            if ($field === 'subjectId') {
                stop(400,"Invalid subjectId provided");
            } else {
                $subjectId = Db::insert('subjects', ['subjectName' => $_POST['subjectName'], 'tahvelSubjectId' => $_POST['tahvelSubjectId'], 'groupId' => $groupId, 'teacherId' => $this->auth->userId]);
                Activity::create(ACTIVITY_CREATE_SUBJECT, $this->auth->userId, $subjectId);
            }
        }else{
            $subject = Db::getFirst("SELECT * FROM subjects WHERE subjectId = ?", [$subjectId]);
            if ($subject['teacherId'] !== $this->auth->userId) {
                $otherTeacher = Db::getFirst("SELECT * FROM users WHERE userId = ?", [$subject['teacherId']]);
                stop(403, 'Subject belongs to ' . $otherTeacher['userName']);
            }
        }

        //Check if assignment exists with given tahvelJournalEntryId
        $assignment = Db::getFirst("SELECT * FROM assignments WHERE tahvelJournalEntryId = ?", [$_POST['tahvelJournalEntryId']]);
        if ($assignment) {
            // Overwrite assignment existing data
            $data = [
                'assignmentDueAt' => $_POST['assignmentDueAt'],
                'assignmentInstructions' => $_POST['assignmentInstructions'],
            ];

            //Prevent other teachers from updating assignments using groups teacherId
            $group = Db::getFirst("SELECT * FROM groups WHERE groupId = ?", [$groupId]);
            if ($group['teacherId'] !== $this->auth->userId) {
                $otherTeacher = Db::getFirst("SELECT * FROM users WHERE userId = ?", [$group['teacherId']]);
                stop(403, 'Assignment belongs to a subject given by ' . $otherTeacher['userName']);
            }

            Db::update('assignments', $data, "assignmentId=$assignment[assignmentId]");
            Activity::create(ACTIVITY_UPDATE_ASSIGNMENT, $this->auth->userId, $assignment['assignmentId']);

            stop(200, $assignment['assignmentId']);
        }

        // Create assignment
        $data = [
            'subjectId' => $subjectId,
            'assignmentName' => $_POST['assignmentInstructions'],
            'tahvelJournalEntryId' => $_POST['tahvelJournalEntryId'],
            'assignmentDueAt' => $_POST['assignmentDueAt'],
            'assignmentInstructions' => $_POST['assignmentInstructions'],
        ];

        $assignmentId = Db::insert('assignments', $data);
        Activity::create(ACTIVITY_CREATE_ASSIGNMENT, $this->auth->userId, $assignmentId);

        stop(200, $assignmentId);
    }

    function checkStudentsGrades()
    {
        $journalEntries = $_POST['journalEntries'];
        $students = $_POST['students'];

        // Ассоциативный массив, где ключ - journalEntryId
        $mismatchedGradesInfo = [];

        if (!empty($journalEntries)) {
            foreach ($journalEntries as $entry) {
                $assignmentId = Db::getOne(
                    "SELECT assignmentId FROM kriit.assignments WHERE tahvelJournalEntryId = ?",
                    [$entry['id']]
                );

                if (!$assignmentId) {
                    continue;
                }

                foreach ($entry['journalStudentResults'] as $result) {
                    $studentData = array_filter($students, function ($student) use ($result) {
                        return $student['id'] === $result['studentId'];
                    });

                    if (empty($studentData)) {
                        continue;
                    }

                    $studentData = reset($studentData);

                    $userId = Db::getOne(
                        "SELECT userId FROM kriit.users WHERE userName = ? AND groupId = (SELECT groupId FROM kriit.groups WHERE groupName = ?)",
                        [$studentData['name'], $studentData['studentGroup']]
                    );

                    if (!$userId) {
                        continue;
                    }

                    $tahvelGrade = str_replace("KUTSEHINDAMINE_", "", $result['gradeCode']);

                    $currentGrade = Db::getOne(
                        "SELECT userGrade FROM kriit.userAssignments WHERE userId = ? AND assignmentId = ?",
                        [$userId, $assignmentId]
                    );

                    if ($tahvelGrade != $currentGrade) {
                        if (!isset($mismatchedGradesInfo[$entry['id']])) {
                            $mismatchedGradesInfo[$entry['id']] = [
                                'assignmentName' => $entry['name'],
                                'students' => []
                            ];
                        }

                        $mismatchedGradesInfo[$entry['id']]['students'][] = [
                            'studentId' => $studentData['id'],
                            'studentName' => $studentData['name'],
                            'kriitGrade' => $currentGrade,
                            'tahvelGrade' => $tahvelGrade
                        ];
                    }
                }
            }
        }

        stop(200, ['mismatchedGradesInfo' => $mismatchedGradesInfo]);
    }

}
