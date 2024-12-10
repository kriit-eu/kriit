<?php namespace App\api;

use App\Activity;
use App\Assignment;
use App\Controller;
use App\Db;

// add /api/groups to the URL

class assignments extends Controller
{

    function addOrUpdate()
    {

        $validationError = $this->validateAssignmentRequestData();
        if ($validationError) {
            stop($validationError['code'], $validationError['message']);
        }

        // Construct parameterized query to check if group exists based on either groupId or groupName which ever is provided
        $groupId = $this->addAssignmentGroup();

        $currentTeacher = Db::getFirst("SELECT userId, userName FROM users WHERE userId = ?", [$this->auth->userId]);

        $subjectId = $this->addAssignmentSubject($groupId);

        $this->addAssignmentTeachers();

        //Check if assignment exists with given tahvelJournalEntryId
        $existingAssignment = Db::getFirst("SELECT * FROM assignments WHERE tahvelJournalEntryId = ?", [$_POST['tahvelJournalEntryId']]);
        $assignmentName = mb_strlen($_POST['assignmentInstructions']) > 50 ? mb_substr($_POST['assignmentInstructions'], 0, 47) . '...' : $_POST['assignmentInstructions'];

        if ($existingAssignment) {
            $this->updateAssignment($assignmentName, $currentTeacher, $existingAssignment);
            stop(200, $existingAssignment['assignmentId']);
        } else {

            // Create assignment
            $data = [
                'subjectId' => $subjectId,
                'assignmentName' => $assignmentName,
                'tahvelJournalEntryId' => $_POST['tahvelJournalEntryId'],
                'assignmentDueAt' => $_POST['assignmentDueAt'],
                'assignmentInstructions' => $_POST['assignmentInstructions'],
            ];

            $assignmentId = Db::insert('assignments', $data);
            Activity::create(ACTIVITY_CREATE_ASSIGNMENT, $this->auth->userId, $assignmentId);
            $this->saveMessage($assignmentId, $currentTeacher['userId'], "$currentTeacher[userName] lõi ülesande '$assignmentName'.", true);

            stop(200, $assignmentId);
        }
    }

    private function updateAssignment($assignmentName, $currentTeacher, $existingAssignment): void
    {
        // Overwrite assignment existing data
        $data = [
            'assignmentName' => $assignmentName,
            'assignmentDueAt' => $_POST['assignmentDueAt'],
            'assignmentInstructions' => $_POST['assignmentInstructions'],
        ];

        Db::update('assignments', $data, "assignmentId=$existingAssignment[assignmentId]");

        if ($existingAssignment['assignmentName'] !== $assignmentName) {
            $this->saveMessage($existingAssignment['assignmentId'], $currentTeacher['userId'], "$currentTeacher[userName] muutis ülesande nimeks '$assignmentName'.", true);
            Activity::create(ACTIVITY_UPDATE_ASSIGNMENT, $this->auth->userId, $existingAssignment['assignmentId'], "Changed assignment name from $existingAssignment[assignmentName] to $assignmentName");
        }

        if ($existingAssignment['assignmentInstructions'] !== $_POST['assignmentInstructions']) {
            $this->saveMessage($existingAssignment['assignmentId'], $currentTeacher['userId'], "$currentTeacher[userName] muutis ülesande juhendiks '$_POST[assignmentInstructions]'.", true);
            Activity::create(ACTIVITY_UPDATE_ASSIGNMENT, $this->auth->userId, $existingAssignment['assignmentId'], "Changed assignment instructions from $existingAssignment[assignmentInstructions] to $_POST[assignmentInstructions]");
        }

        if ($existingAssignment['assignmentDueAt'] !== $_POST['assignmentDueAt']) {
            $date = date('d.m.Y', strtotime($_POST['assignmentDueAt']));
            $this->saveMessage($existingAssignment['assignmentId'], $currentTeacher['userId'], "$currentTeacher[userName] muutis ülesande tähtajaks '$date'.", true);
            Activity::create(ACTIVITY_UPDATE_ASSIGNMENT, $this->auth->userId, $existingAssignment['assignmentId'], "Changed assignment due date from $existingAssignment[assignmentDueAt] to $_POST[assignmentDueAt]");
        }
    }

    private function addAssignmentTeachers(): void
    {
        $teachers = $_POST['teachersData'];
        if ($teachers) {
            foreach ($teachers as $teacher) {
                $existingTeacher = Db::getFirst("SELECT * FROM users WHERE userName = ?", [$teacher['teacherName']]);
                if (!$existingTeacher) {
                    Db::insert('users', ['userName' => $teacher['teacherName'], 'userIsTeacher' => 1, 'userIsAdmin' => 0, 'userPersonalCode' => $teacher['teacherPersonalCode'], 'userEmail' => $teacher['teacherEmail']]);
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
    }

    private function addAssignmentSubject($groupId): int
    {
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

        return $subjectId;
    }

    private function addAssignmentGroup()
    {
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

        return $groupId;
    }

    private function validateAssignmentRequestData(): ?array
    {
        // Check that either subjectId or subjectName is provided but not both
        if (empty($_POST['subjectId']) && empty($_POST['subjectName'])) {
            return ['code' => 400, 'message' => 'subjectId or subjectName is required'];
        }

        if (!empty($_POST['subjectId']) && !empty($_POST['subjectName'])) {
            return ['code' => 400, 'message' => 'subjectId or subjectName is required but not both'];
        }

        // Check that either groupId or groupName is provided but not both
        if (empty($_POST['groupId']) && empty($_POST['groupName'])) {
            return ['code' => 400, 'message' => 'groupId or groupName is required'];
        }

        if (!empty($_POST['groupId']) && !empty($_POST['groupName'])) {
            return ['code' => 400, 'message' => 'groupId or groupName is required but not both'];
        }

        // Check if tahvelSubjectId is provided
        if (empty($_POST['tahvelSubjectId'])) {
            return ['code' => 400, 'message' => 'tahvelSubjectId is required'];
        }

        // Check if tahvelJournalEntryId is provided
        if (empty($_POST['tahvelJournalEntryId'])) {
            return ['code' => 400, 'message' => 'tahvelJournalEntryId is required'];
        }

        // if assignmentDueAt is provided, check if it is a valid date
        if (!empty($_POST['assignmentDueAt'])) {
            $dateParts = explode('-', $_POST['assignmentDueAt']);
            if (count($dateParts) !== 3 || !checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
                return ['code' => 400, 'message' => 'Invalid assignmentDueAt'];
            }
        }

        return null;
    }


    private function saveMessage($assignmentId, $userId, $content, $isNotification = false): void
    {
        Db::insert('messages', ['assignmentId' => $assignmentId, 'userId' => $userId, 'content' => $content, 'CreatedAt' => date('Y-m-d H:i:s'), 'isNotification' => $isNotification]);

    }

    function checkStudentsGradesInfo(): void
    {
        $journalEntries = $_POST['journalEntries'];
        $students = $_POST['students'];
        $groupName = $_POST['groupName'];
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
                    "SELECT ua.userGrade, ua.comment, ua.userId
                 FROM users u
                 JOIN groups g ON u.groupId = g.groupId
                 JOIN userAssignments ua ON u.userId = ua.userId
                 WHERE u.userName = ? AND g.groupName = ? AND ua.assignmentId = ?",
                    [$studentData['name'], $groupName, $assignmentId]
                );

                if (!$assignmentData) continue;

                $currentGrade = $assignmentData['userGrade'] ?? "puudub";
                $currentComment = empty($assignmentData['comment']) ? '' : BASE_URL . 'assignments/' . $assignmentId . '/' . $assignmentData['userId'];

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


    function deleteAssignmentByTahvelJournalId(): void
    {
        if (empty($_POST['tahvelJournalEntryId'])) {
            stop(400, 'Invalid tahvelJournalEntryId');
        }

        $this->deleteAllAssignmentDependentData($_POST['tahvelJournalEntryId']);

        try {
            Db::delete('assignments', 'tahvelJournalEntryId = ?', [$_POST['tahvelJournalEntryId']]);
            Activity::create(ACTIVITY_DELETE_ASSIGNMENT, $this->auth->userId, null, "Deleted assignment with tahvelJournalEntryId: $_POST[tahvelJournalEntryId]");
        } catch (\Exception $e) {
            stop(400, $e->getMessage());
        }

        stop(200, 'Assignment deleted');
    }

    private function deleteAllAssignmentDependentData($tahvelJournalEntryId): void
    {
        try {
            $criteria = Db::getAll("SELECT criterionId FROM criteria WHERE assignmentId = (SELECT assignmentId FROM assignments WHERE tahvelJournalEntryId = ?)", [$tahvelJournalEntryId]);
            foreach ($criteria as $criterion) {
                Db::delete('userDoneCriteria', 'criterionId = ?', [$criterion['criterionId']]);
            }

            Db::delete('criteria', 'assignmentId = (SELECT assignmentId FROM assignments WHERE tahvelJournalEntryId = ?)', [$tahvelJournalEntryId]);
            Db::delete('messages', 'assignmentId = (SELECT assignmentId FROM assignments WHERE tahvelJournalEntryId = ?)', [$tahvelJournalEntryId]);
            Db::delete('userAssignments', 'assignmentId = (SELECT assignmentId FROM assignments WHERE tahvelJournalEntryId = ?)', [$tahvelJournalEntryId]);
        } catch (\Exception $e) {
            stop(400, $e->getMessage());
        }

    }

    function saveUserDoneCriteria(): void
    {
        $studentId = $this->auth->userId;
        $criterionId = $_POST['criterionId'];
        $done = $_POST['done'];

        // Check if the criterion exists
        if (!Db::getOne('SELECT criterionId FROM criteria WHERE criterionId = ?', [$criterionId])) {
            stop(400, 'Invalid criterionId');
        }

        // Delete the criterion first
        Db::delete('userDoneCriteria', 'userId = ? AND criterionId = ?', [$studentId, $criterionId]);

        // Mark criterion as completed if the checkbox was checked
        if ($done === 'true') {
            Db::insert('userDoneCriteria', ['userId' => $studentId, 'criterionId' => $criterionId]);
        }

        stop(200);

    }

    function addComment($isSolution = false)
    {
        validate($_POST['comment'], 'Invalid comment. It must be a string.', IS_STRING);
        validate($_POST['assignmentId'], 'Invalid assignmentId.');

        Db::insert('assignmentComments', [
            'assignmentId' => $_POST['assignmentId'],
            'userId' => $this->auth->userId,
            'isSolution' => $isSolution,
            'assignmentComment' => $_POST['comment'],
            'assignmentCommentCreatedAt' => date('Y-m-d H:i:s')
        ]);

        stop(200);
    }

    function submitSolution()
    {
        validate($_POST['solutionUrl'], 'Invalid solutionUrl.', IS_STRING);
        validate($_POST['assignmentId'], 'Invalid assignmentId.');

        Assignment::userIsStudent($this->auth->userId, $_POST['assignmentId']) || stop(403, 'Teil pole õigusi sellele tegevusele.');

        $existAssignment = Db::getFirst('SELECT * FROM userAssignments JOIN users USING(userId) WHERE userId = ? AND assignmentId = ? ', [$this->auth->userId, $_POST['assignmentId']]);
        if (!$existAssignment) {
            Db::insert('userAssignments', ['userId' => $this->auth->userId, 'assignmentId' => $_POST['assignmentId'], 'assignmentStatusId' => 2, 'solutionUrl' => $_POST['solutionUrl']]);
            Activity::create(ACTIVITY_SUBMIT_ASSIGNMENT, $this->auth->userId, $_POST['assignmentId'], "esitas ülesande lahenduse");
            $studentName = Db::getOne('SELECT userName FROM users WHERE userId = ?', [$this->auth->userId]);
        } else {
            Db::update('userAssignments', ['assignmentStatusId' => 2, 'solutionUrl' => $_POST['solutionUrl']], 'userId = ? AND assignmentId = ?', [$this->auth->userId, $_POST['solutionUrl']]);
            Activity::create(ACTIVITY_SUBMIT_ASSIGNMENT, $this->auth->userId, $_POST['assignmentId'], "esitas ülesande lahenduse uuesti");
            $studentName = $existAssignment['userName'];
        }

        // Add comment that includes assignment link
        if (empty($_POST['comment'])) {
            $_POST['comment'] = "<br><br>Lahenduse link: <a href='$_POST[solutionUrl]'>$_POST[solutionUrl]</a><br>";
        }
        Assignment::addComment($assignmentId, $this->auth->userId, $this->auth->userId, $_POST['comment'], true);


        if ($existAssignment['assignmentStatusId'] !== 2) {
            $mailData = $this->getSenderNameAndReceiverEmail($this->auth->userId, $existAssignment['userId']);
            $studentName = $mailData['senderName'];
            $teacherMail = $mailData['receiverMail'];
            $assignment = $this->getAssignmentDetails($_POST['solutionUrl']);

            $emailBody = $existAssignment['userGrade'] ? ($existAssignment['userGrade'] === 'MA' || is_numeric(intval($existAssignment['userGrade']) < 3)) ?
                sprintf(
                    "Õpilane <strong>%s</strong> parandas ülesande '<a href=\"" . BASE_URL . "assignments/%s\"><strong>%s</strong></a> lahendust.<br><br>Lahenduse link: <a href='%s'>%s</a><br>",
                    $studentName,
                    $assignment['assignmentId'],
                    $assignment['assignmentName'],
                    $_POST['solutionUrl'],
                    $_POST['solutionUrl']
                ) :
                sprintf(
                    "Õpilane <strong>%s</strong> esitas lahenduse ülesandele '<a href=\"" . BASE_URL . "assignments/%s\"><strong>%s</strong></a>'.<br><br>Lahenduse link: <a href='%s'>%s</a><br>", $studentName,
                    $assignment['assignmentId'],
                    $assignment['assignmentName'],
                    $_POST['solutionUrl'],
                    $_POST['solutionUrl']
                ) :

                $subject = $existAssignment['userGrade'] ? ($existAssignment['userGrade'] === 'MA' || is_numeric(intval($existAssignment['userGrade']) < 3)) ?
                    $assignment['subjectName'] . ": $studentName parandas ülesande '" . $assignment['assignmentName'] . "' lahendust" :
                    $assignment['subjectName'] . ": $studentName esitas lahenduse ülesandele '" . $assignment['assignmentName'] . "'" :
                    $assignment['subjectName'] . ": $studentName esitas lahenduse ülesandele '" . $assignment['assignmentName'] . "'";

            if ($existAssignment['userGrade']) {
                $this->sendNotificationToEmail($existAssignment['userEmail'], $subject, $emailBody);
            }
        }
        stop(200);
    }
}
