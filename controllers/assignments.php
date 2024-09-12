<?php namespace App;

class assignments extends Controller
{
    public $template = 'master';

    public function view()
    {
        $this->isStudent = $this->auth->groupId && !$this->auth->userIsAdmin && !$this->auth->userIsTeacher;
        $this->isTeacher = $this->auth->userIsTeacher;
        $assignmentId = $this->getId();

        $statusClassMap = [
            'Esitamata' => $this->isStudent ? 'yellow-cell' : '',
            'Ülevaatamata' => $this->auth->userIsTeacher ? 'red-cell' : '',
        ];

        $data = Db::getAll("
            SELECT
                a.assignmentId, a.assignmentName, a.assignmentInstructions, a.assignmentDueAt,
                c.criterionId, c.criterionName,
                u.userId AS studentId, u.userName AS studentName, u.groupId,
                ua.userGrade, ua.assignmentStatusId, ua.solutionUrl, ua.comment,
                ast.statusName AS assignmentStatusName,
                udc.criterionId AS userDoneCriterionId,
                subj.teacherId AS teacherId,
                t.userName AS teacherName
            FROM assignments a
            JOIN criteria c ON c.assignmentId = a.assignmentId
            JOIN subjects subj ON a.subjectId = subj.subjectId
            JOIN users t ON subj.teacherId = t.userId
            JOIN groups g ON subj.groupId = g.groupId
            JOIN users u ON u.groupId = g.groupId
            LEFT JOIN userAssignments ua ON ua.assignmentId = a.assignmentId AND ua.userId = u.userId
            LEFT JOIN assignmentStatuses ast ON ua.assignmentStatusId = ast.assignmentStatusId
            LEFT JOIN userDoneCriteria udc ON udc.criterionId = c.criterionId AND udc.userId = u.userId
            WHERE a.assignmentId = ?
            ORDER BY u.userName, c.criterionId
        ", [$assignmentId]);

        $assignment = [
            'assignmentId' => $assignmentId,
            'assignmentName' => null,
            'assignmentInstructions' => null,
            'assignmentDueAt' => null,
            'teacherId' => null,
            'teacherName' => null,
            'criteria' => [],
            'students' => [],
            'messages' => []
        ];

        foreach ($data as $row) {
            if (empty($assignment['assignmentName'])) {
                $assignment['assignmentName'] = $row['assignmentName'];
                $assignment['assignmentInstructions'] = $row['assignmentInstructions'];
                $assignment['assignmentDueAt'] = date('d.m.Y', strtotime($row['assignmentDueAt']));
                $assignment['teacherId'] = $row['teacherId'];
                $assignment['teacherName'] = $row['teacherName'];
            }

            $studentId = $row['studentId'];
            $criteriaId = $row['criterionId'];

            if ($this->isStudent && $this->auth->userId !== $studentId) {
                continue;
            }

            if (!isset($assignment['criteria'][$criteriaId])) {
                $assignment['criteria'][$criteriaId] = [
                    'criteriaId' => $criteriaId,
                    'criteriaName' => $row['criterionName']
                ];
            }

            if (!isset($assignment['students'][$studentId])) {
                $assignment['students'][$studentId] = [
                    'studentId' => $studentId,
                    'studentName' => $row['studentName'],
                    'grade' => !empty($row['userGrade']) ? trim($row['userGrade']) : '',
                    'assignmentStatusName' => $row['assignmentStatusName'] ?? 'Esitamata',
                    'initials' => mb_substr($row['studentName'], 0, 1) . mb_substr($row['studentName'], mb_strrpos($row['studentName'], ' ') + 1, 1),
                    'solutionUrl' => $row['solutionUrl'],
                    'comment' => $row['comment'],
                    'userDoneCriteria' => [],
                    'userDoneCriteriaCount' => 0,
                    'class' => '',
                    'tooltipText' => '',
                    'studentActionButtonName' => $row['solutionUrl'] === null ? 'Esita' : 'Muuda',
                ];
            }

            $isCompleted = $row['userDoneCriterionId'] !== null;
            $assignment['students'][$studentId]['userDoneCriteria'][$criteriaId] = [
                'criterionId' => $criteriaId,
                'criterionName' => $row['criterionName'],
                'completed' => $isCompleted
            ];

            if ($isCompleted) {
                $assignment['students'][$studentId]['userDoneCriteriaCount']++;
            }

            $statusName = $row['assignmentStatusName'] ?? 'Esitamata';
            $grade = !empty($row['userGrade']) ? trim($row['userGrade']) : '';
            $isLowGrade = $grade == 'MA' || (is_numeric($grade) && intval($grade) < 3);
            $isGraded = !$isLowGrade;
            $isEvaluated = isset($row['assignmentStatusName']) && $row['assignmentStatusName'] === 'Hinnatud';

            $assignment['students'][$studentId]['isDisabledStudentActionButton'] = ($isEvaluated && $isGraded) ? 'disabled' : '';

            $daysRemaining = (int)(new \DateTime())->diff(new \DateTime($row['assignmentDueAt']))->format('%r%a');
            $class = $daysRemaining < 0 ?
                (($this->isStudent && $statusName == 'Esitamata') ||
                ($this->isStudent && $isLowGrade) ||
                ($this->isTeacher && $statusName !== 'Hinnatud') ? 'red-cell' : '') :
                ($isLowGrade ? 'red-cell' : ($statusClassMap[$statusName] ?? ''));

            $tooltipText = $statusName ?? 'Esitamata';

            $assignment['students'][$studentId]['class'] = $class;
            $assignment['students'][$studentId]['tooltipText'] = $tooltipText;
        }

        // Separate query for fetching messages
        $messages = Db::getAll("
            SELECT
                m.messageId, m.content AS messageContent, m.userId AS messageUserId, mu.userName AS messageUserName, m.CreatedAt, m.isNotification
            FROM messages m
            LEFT JOIN users mu ON mu.userId = m.userId
            WHERE m.assignmentId = ?
            ORDER BY m.CreatedAt
        ", [$assignmentId]);

        // Add the messages to the assignment
        foreach ($messages as $message) {
            $assignment['messages'][] = [
                'messageId' => $message['messageId'],
                'content' => $message['messageContent'],
                'userId' => $message['messageUserId'],
                'userName' => $message['messageUserName'],
                'createdAt' => $this->formatMessageDate($message['CreatedAt']),
                'isNotification' => $message['isNotification']
            ];
        }

        $this->assignment = $assignment;
    }

    // Helper function to check if a date is today
    private function formatMessageDate($date): string
    {
        if (date('Y-m-d') === date('Y-m-d', strtotime($date))) {
            return 'Täna ' . date('H:i', strtotime($date));
        } elseif (date('Y-m-d', strtotime('-1 day')) === date('Y-m-d', strtotime($date))) {
            return 'Eile ' . date('H:i', strtotime($date));
        } else {
            return date('d.m.Y H:i', strtotime($date));
        }
    }

    function ajax_saveAssignmentGrade()
    {
        $this->auth->userIsAdmin || $this->auth->userId == $_POST['teacherId'] || stop(403, 'Permission denied');
        $assignmentId = $_POST['assignmentId'];
        $studentId = $_POST['studentId'];
        $grade = $_POST['grade'] ?? null;
        $criteria = $_POST['criteria'] ?? [];
        $comment = $_POST['comment'] ?? '';
        $existAssignment = Db::getFirst('SELECT * FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
        if ($grade !== null && !in_array($grade, ['1', '2', '3', '4', '5', 'A', 'MA'])) {
            stop(400, 'Invalid grade');
        }

        if (count($criteria) !== 0){

            $falseCriteria = array_keys(array_filter($criteria, function ($value) {
                return $value === 'false';
            }));

            if (count($falseCriteria) > 0) {
                $grade = 'MA';
            }

            foreach ($falseCriteria as $criterionId) {
                $criterionName = Db::getOne('SELECT criterionName FROM criteria WHERE criterionId = ?', [$criterionId]);
                $message = "$_POST[teacherName] eemaldas õpilaselt $_POST[studentName] kriteeriumi $criterionName.";
                Db::delete('userDoneCriteria', 'userId = ? AND criterionId = ?', [$studentId, $criterionId]);
                $this->saveMessage($assignmentId, $_POST['teacherId'], $message);
            }
        }
        if ($existAssignment['comment'] !== $comment && $comment !== '') {
            $message = "$_POST[teacherName] lisas õpilasele $_POST[studentName] tagasisideks: '$comment'";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
        }

        if (!$existAssignment['userGrade']) {
            $message = "$_POST[teacherName] lisas õpilasele $_POST[studentName] hindeks: $grade";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
        }elseif ($existAssignment['userGrade'] !== $grade) {
            $message = "$_POST[teacherName] muutis õpilase $_POST[studentName] hinnet: $existAssignment[userGrade] -> $grade";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
        }

        Db::update('userAssignments', ['userGrade' => $grade, 'assignmentStatusId' => 3, 'comment' => $comment], 'userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);

        stop(200, 'Grade saved');
    }

    function ajax_saveStudentSolutionUrl()
    {
        $this->auth->userIsAdmin || $this->auth->userId == intval($_POST['studentId']) || stop(403, 'Permission denied');
        $assignmentId = $_POST['assignmentId'];
        $studentId = $_POST['studentId'];
        $solutionUrl = $_POST['solutionUrl'];
        $criteria = $_POST['criteria'];

        $isSaveAllowed = $this->checkStudentSavePermission($studentId, $_POST['assignmentId']);
        if (!$isSaveAllowed) {
            stop(403, 'Save not allowed');
        }

        $this->saveCriteria($studentId, $criteria);

        Db::update('userAssignments', ['solutionUrl' => $solutionUrl], 'userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);

        stop(200, 'Solution url saved');
    }

    function ajax_saveStudentCriteria()
    {
        $this->auth->userIsAdmin || $this->auth->userId == $_POST['studentId'] || stop(403, 'Permission denied');
        $studentId = $_POST['studentId'];
        $criteria = $_POST['criteria'];

        $isSaveAllowed = $this->checkStudentSavePermission($studentId, $_POST['assignmentId']);
        if (!$isSaveAllowed) {
            stop(403, 'Save not allowed');
        }

        $this->saveCriteria($studentId, $criteria);

        stop(200, 'Criteria saved');
    }

    function ajax_saveMessage()
    {
        $this->auth->userIsAdmin || $this->auth->userId == $_POST['userId'] || stop(403, 'Permission denied');
        $assignmentId = $_POST['assignmentId'];
        $userId = $_POST['userId'];
        $content = $_POST['content'];

        $this->saveMessage($assignmentId, $userId, $content);

        stop(200, 'Message saved');
    }

    function ajax_editAssignment()
    {
        $this->auth->userIsAdmin || $this->auth->userId == $_POST['teacherId'] || stop(403, 'Permission denied');
        $assignmentId = $_POST['assignmentId'];
        $assignmentName = $_POST['assignmentName'];
        $assignmentInstructions = $_POST['assignmentInstructions'];
        $assignmentDueAt = $_POST['assignmentDueAt'];

        $existAssignment = Db::getFirst('SELECT * FROM assignments WHERE assignmentId = ?', [$assignmentId]);

        if ($existAssignment['assignmentName'] !== $assignmentName) {
            $message = "$_POST[teacherName] muutis ülesande nimeks '$assignmentName'.";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
        }
        if ($existAssignment['assignmentInstructions'] !== $assignmentInstructions) {
            $message = "$_POST[teacherName] muutis ülesande juhendiks '$assignmentInstructions'.";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
        }

        if ($existAssignment['assignmentDueAt'] !== $assignmentDueAt) {
            $date = date('d.m.Y', strtotime($assignmentDueAt));
            $message = "$_POST[teacherName] muutis ülesande tähtajaks '$date'.";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
        }

        Db::update('assignments', ['assignmentName' => $assignmentName, 'assignmentInstructions' => $assignmentInstructions, 'assignmentDueAt' => $assignmentDueAt], 'assignmentId = ?', [$assignmentId]);

        stop(200, 'Assignment edited');
    }

    function ajax_removeCriterion()
    {
        $this->auth->userIsAdmin || $this->auth->userId == $_POST['teacherId'] || stop(403, 'Permission denied');
        $assignmentId = $_POST['assignmentId'];
        $criterionId = $_POST['criterionId'];

        $criterionName = Db::getOne('SELECT criterionName FROM criteria WHERE criterionId = ?', [$criterionId]);
        $message = "$_POST[teacherName] eemaldas kriteeriumi '$criterionName'.";
        Db::delete('criteria', 'criterionId = ?', [$criterionId]);
        $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);

        stop(200, 'Criterion removed');
    }

    private function saveCriteria($studentId, $criteria)
    {
        foreach ($criteria as $criterionId => $completed) {
            $existCriterion = Db::getOne('SELECT criterionId FROM userDoneCriteria WHERE userId = ? AND criterionId = ?', [$studentId, $criterionId]);
            if (!$existCriterion && $completed === 'true') {
                Db::insert('userDoneCriteria', ['userId' => $studentId, 'criterionId' => $criterionId]);
            } elseif ($existCriterion && $completed === 'false') {
                Db::delete('userDoneCriteria', 'userId = ? AND criterionId = ?', [$studentId, $criterionId]);
            }
        }
    }

    private function checkStudentSavePermission($studentId, $assignmentId)
    {
        $student = Db::getFirst("SELECT ua.assignmentStatusId, ua.userGrade FROM userAssignments ua WHERE ua.userId = ? AND ua.assignmentId = ?", [$studentId, $assignmentId]);
        if ($student['assignmentStatusId'] == 3 && $student['userGrade'] && $student['userGrade'] != 'MA' && intval($student['userGrade']) > 2 ) {
            return false;
        }
        return true;
    }

    private function saveMessage($assignmentId, $userId, $content, $isNotification  = false)
    {
        Db::insert('messages', ['assignmentId' => $assignmentId, 'userId' => $userId, 'content' => $content, 'CreatedAt' => date('Y-m-d H:i:s'), 'isNotification' => $isNotification]);
    }
}
