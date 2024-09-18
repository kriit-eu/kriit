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
                stop(400, "Invalid groupId provided");
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
                stop(400, "Invalid subjectId provided");
            } else {
                $subjectId = Db::insert('subjects', ['subjectName' => $_POST['subjectName'], 'tahvelSubjectId' => $_POST['tahvelSubjectId'], 'groupId' => $groupId, 'teacherId' => $this->auth->userId]);
                Activity::create(ACTIVITY_CREATE_SUBJECT, $this->auth->userId, $subjectId);
            }
        } else {
            $subject = Db::getFirst("SELECT * FROM subjects WHERE subjectId = ?", [$subjectId]);
            if ($subject['teacherId'] !== $this->auth->userId) {
                $otherTeacher = Db::getFirst("SELECT * FROM users WHERE userId = ?", [$subject['teacherId']]);
                stop(403, 'Subject belongs to ' . $otherTeacher['userName']);
            }
        }

        $teachers = $_POST['teachersData'];
        if ($teachers) {
            foreach ($teachers as $teacher) {
                $existingTeacher = Db::getFirst("SELECT * FROM users WHERE userName = ?", [$teacher['teacherName']]);
                if (!$existingTeacher) {
                    $newTeacherId = Db::insert('users', ['userName' => $teacher['teacherName'], 'userIsTeacher' => 1, 'userIsAdmin' => 0, 'userPersonalCode' => $teacher['teacherPersonalCode'], 'userEmail' => $teacher['teacherEmail']]);
                } else {
                    $existingTeacherId = $existingTeacher['userId'];

                    if ($existingTeacher['userPersonalCode'] !== $teacher['teacherPersonalCode']) {
                        Db::update('users', ['userPersonalCode' => $teacher['teacherPersonalCode']], "userId = ?", [$existingTeacherId]);
                    }
                    if ($existingTeacher['userEmail'] !== $teacher['teacherEmail']) {
                        Db::update('users', ['userEmail' => $teacher['teacherEmail']], "userId = ?", [$existingTeacherId]);
                    }
                }

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

    function checkStudentsGradesInfo()
    {
        $journalEntries = $_POST['journalEntries'];
        $students = $_POST['students'];
        $mismatchedGradesInfo = [];

        if (empty($journalEntries)) {
            stop(200, ['mismatchedGradesInfo' => $mismatchedGradesInfo]);
        }

        foreach ($journalEntries as $entry) {
            $assignmentId = Db::getOne(
                "SELECT assignmentId FROM kriit.assignments WHERE tahvelJournalEntryId = ?",
                [$entry['id']]
            );

            if (!$assignmentId) continue;

            foreach ($students as $studentData) {
                $result = array_filter($entry['journalStudentResults'], fn($res) => $res['studentId'] === $studentData['id']);
                $result = !empty($result) ? reset($result) : null;

                $assignmentData = Db::getFirst(
                    "SELECT ua.userGrade, ua.comment
                 FROM kriit.users u
                 JOIN kriit.groups g ON u.groupId = g.groupId
                 JOIN kriit.userAssignments ua ON u.userId = ua.userId
                 WHERE u.userName = ? AND g.groupName = ? AND ua.assignmentId = ?",
                    [$studentData['name'], $studentData['studentGroup'], $assignmentId]
                );

                if (!$assignmentData) continue;

                $currentGrade = $assignmentData['userGrade'] ?? null;
                $currentComment = $assignmentData['comment'] ?? '';

                $tahvelGrade = $result ? str_replace("KUTSEHINDAMINE_", "", $result['gradeCode']) : "puudub";
                $tahvelComment = $result['addInfo'] ?? '';

                if ($tahvelGrade !== $currentGrade || $tahvelComment !== $currentComment) {
                    if (!isset($mismatchedGradesInfo[$entry['id']])) {
                        $mismatchedGradesInfo[$entry['id']] = [
                            'assignmentName' => $entry['name'],
                            'students' => []
                        ];
                    }

                    $studentInfo = [
                        'studentId' => $studentData['id'],
                        'studentName' => $studentData['name'],
                    ];

                    if ($tahvelGrade !== $currentGrade) {
                        $studentInfo['kriitGrade'] = $currentGrade;
                        $studentInfo['tahvelGrade'] = $tahvelGrade;
                    }

                    if ($tahvelComment !== $currentComment) {
                        $studentInfo['kriitComment'] = $currentComment;
                        $studentInfo['tahvelComment'] = $tahvelComment;
                    }

                    $mismatchedGradesInfo[$entry['id']]['students'][] = $studentInfo;
                }
            }
        }

        stop(200, ['mismatchedGradesInfo' => $mismatchedGradesInfo]);
    }

}
