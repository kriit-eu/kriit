<?php namespace App\api;

use App\Activity;
use App\Assignment;
use App\Controller;
use App\Db;
use App\Mail;
use App\Validate;

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

        // Validate required fields
        @Validate::id($_POST['tahvelSubjectId'], 'tahvelSubjectId is required');
        @Validate::id($_POST['tahvelJournalEntryId'], 'tahvelJournalEntryId is required');

        // Validate optional fields
        if (!empty($_POST['assignmentDueAt'])) {
            @Validate::date($_POST['assignmentDueAt'], 'Invalid assignmentDueAt');
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

                // TODO:
                $assignmentData = Db::getFirst(
                    "SELECT ua.userGrade, ua.userId,
                            IF(ua.userGrade = 'MA' OR ua.userGrade IN ('1', '2'),
                                (SELECT ac.assignmentCommentText
                                 FROM assignmentComments ac
                                 WHERE ac.assignmentId = ua.assignmentId 
                                 AND ac.userId = ua.userId
                                 AND ac.assignmentCommentTypeId = 4
                                 ORDER BY ac.assignmentCommentCreatedAt DESC
                                 LIMIT 1),
                                NULL
                            ) as rejectionComment
                     FROM users u
                     JOIN groups g ON u.groupId = g.groupId
                     JOIN userAssignments ua ON u.userId = ua.userId
                     LEFT JOIN assignmentComments ac ON ua.assignmentId = ac.assignmentId AND ua.userId = ac.userId
                     WHERE u.userName = ? AND g.groupName = ? AND ua.assignmentId = ?",
                    [$studentData['name'], $groupName, $assignmentId]
                );

                if (!$assignmentData) continue;

                $currentGrade = $assignmentData['userGrade'] ?? "puudub";
                $currentComment = $assignmentData['rejectionComment'];

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
        @Validate::id($_POST['tahvelJournalEntryId'], 'Invalid tahvelJournalEntryId.');

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
        @Validate::id($_POST['criterionId'], 'Invalid criterionId.');
        @Validate::bool($_POST['done'], 'Invalid done parameter.');
        @Validate::id($_POST['studentId'], 'Invalid studentId.', true);

        // If the user is not an admin and not the teacher teaching the subject that the assignment belongs to and studentID is not the same as the current user, return an error
        if (!$this->auth->userIsAdmin && !Assignment::userIsTeacher($this->auth->userId, $_POST['assignmentId']) && $_POST['studentId'] !== $this->auth->userId) {
            stop(403, 'You are not authorized to change this criterion.');
        }

        // Check if the criterion exists
        if (!Db::getOne('SELECT criterionId FROM criteria WHERE criterionId = ?', [$_POST['criterionId']])) {
            stop(400, 'Invalid criterionId');
        }

        // Delete the criterion first
        Db::delete('userDoneCriteria', 'userId = ? AND criterionId = ?', [$this->auth->userId, $_POST['criterionId']]);

        // Mark criterion as completed if the checkbox was checked
        if ($_POST['done'] === true) {
            Db::insert('userDoneCriteria', ['userId' => $this->auth->userId, 'criterionId' => $_POST['criterionId']]);
        }

        stop(200);

    }

    function submitSolution()
    {
        @Validate::string($_POST['solutionUrl'], 'Vigane lahenduse URL');
        @Validate::id($_POST['assignmentId'], 'Vigane ülesande ID');
        @Validate::url($_POST['solutionUrl']);

        Assignment::userIsStudent($this->auth->userId, $_POST['assignmentId']) || 
            stop(403, 'Teil pole õigusi sellele tegevusele.');

        $data = ['assignmentStatusId' => 2, 'userGrade' => null, 'solutionUrl' => $_POST['solutionUrl']];

        $assignmentExisted = Db::getFirst(
            'SELECT * FROM userAssignments JOIN users USING(userId) WHERE userId = ? AND assignmentId = ?',
            [$this->auth->userId, $_POST['assignmentId']]);

        $assignmentExisted
            ? Db::update('userAssignments', $data, 'userId = ? AND assignmentId = ?', [$this->auth->userId, $_POST['assignmentId']])
            : Db::insert('userAssignments', $data + ['userId' => $this->auth->userId, 'assignmentId' => $_POST['assignmentId']]);

        Activity::create(
            ACTIVITY_SUBMIT_ASSIGNMENT,
            $this->auth->userId,
            $_POST['assignmentId'],
            "esitas ülesande lahenduse" . ($assignmentExisted ? " uuesti" : ""));

        Assignment::addComment(
            $_POST['assignmentId'],
            $this->auth->userId,
            $this->auth->userId,
            "[$_POST[solutionUrl]]($_POST[solutionUrl])",
            ASSIGNMENT_COMMENT_TYPE_PROPOSED_SOLUTION);

        $subject = Db::getFirst(
            'SELECT u.userEmail teacherEmail, s.subjectName, a.assignmentName, ua.userGrade 
             FROM users u
             JOIN subjects s ON u.userId = s.teacherId
             JOIN assignments a ON s.subjectId = a.subjectId
             LEFT JOIN userAssignments ua ON a.assignmentId = ua.assignmentId AND ua.userId = ?
             WHERE a.assignmentId = ?',
            [$this->auth->userId, $_POST['assignmentId']]);

        if (!empty($subject['teacherEmail'])) {
            $emailSubject = $subject['userGrade'] ? 
                ($subject['userGrade'] === 'MA' || (is_numeric($subject['userGrade']) && intval($subject['userGrade']) < 3)) ?
                    $subject['subjectName'] . ": {$this->auth->userName} parandas ülesande '{$subject['assignmentName']}' lahendust" :
                    $subject['subjectName'] . ": {$this->auth->userName} esitas lahenduse ülesandele '{$subject['assignmentName']}'" :
                $subject['subjectName'] . ": {$this->auth->userName} esitas lahenduse ülesandele '{$subject['assignmentName']}'";

            $verb = $assignmentExisted ? 'parandas' : 'esitas';
            $body = sprintf(
                'Õpilane <strong>%s</strong> %s ülesande <a href="%s/assignments/%d"><strong>%s</strong></a> lahenduse.<br><br>Lahenduse link: <a href="%s">%s</a>',
                $this->auth->userName,
                $verb,
                BASE_URL,
                $_POST['assignmentId'],
                $subject['assignmentName'],
                $_POST['solutionUrl'],
                $_POST['solutionUrl']
            );
            Mail::send($subject['teacherEmail'], $emailSubject, $body);
        }
        stop(200);
    }

    function addComment()
    {
        @Validate::id($_POST['assignmentId'], 'Invalid assignmentId.');
        @Validate::string($_POST['comment'], 'Invalid comment.');
        @Validate::id($_POST['studentId'], 'Invalid studentId.', false);

        // Read studentId from POST or use auth->userId
        $studentId = $_POST['studentId'] ?? $this->auth->userId;

        Assignment::addComment(
            $_POST['assignmentId'],
            $studentId,
            $this->auth->userId,
            $_POST['comment']
        );

        stop(200);

    }

}
