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
                    'solutionUrl' => isset($row['solutionUrl']) ? trim($row['solutionUrl']) : '',
                    'comment' => $row['comment'],
                    'userDoneCriteria' => [],
                    'userDoneCriteriaCount' => 0,
                    'class' => '',
                    'tooltipText' => '',
                    'studentActionButtonName' => (isset($row['solutionUrl']) && !empty(trim($row['solutionUrl']))) ? 'Muuda' : 'Esita'
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
            $isEvaluated = isset($row['assignmentStatusName']) && $row['assignmentStatusName'] === 'Hinnatud';

            $assignment['students'][$studentId]['isDisabledStudentActionButton'] = ($isEvaluated && !$isLowGrade) ? 'disabled' : '';

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

        if (count($criteria) !== 0) {

            $falseCriteria = array_keys(array_filter($criteria, function ($value) {
                return $value === 'false';
            }));

            if (count($falseCriteria) > 0) {
                $grade = 'MA';
            }

            $this->saveStudentCriteria();
        }

        if ($existAssignment['comment'] !== $comment && $comment !== '') {
            $message = "$_POST[teacherName] lisas õpilasele $_POST[studentName] tagasisideks: '$comment'";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
        }

        if (!$existAssignment['userGrade']) {
            $message = "$_POST[teacherName] lisas õpilasele $_POST[studentName] hindeks: $grade";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
        } elseif ($existAssignment['userGrade'] !== $grade) {
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

        $isSaveAllowed = $this->checkIfStudentHasAllCriteria();
        if (!$isSaveAllowed) {
            stop(403, 'Save not allowed');
        }

        $this->saveStudentCriteria();

        // Check if userAssignment already exists
        $existAssignment = Db::getFirst('SELECT * FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
        if (!$existAssignment) {
            Db::insert('userAssignments', ['userId' => $studentId, 'assignmentId' => $assignmentId, 'assignmentStatusId' => 2, 'solutionUrl' => $solutionUrl]);
        }else {
            Db::update('userAssignments', ['assignmentStatusId' => 2 ,'solutionUrl' => $solutionUrl], 'userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
        }


        $details = $this->getAssignmentDetails($assignmentId, $studentId, $_POST['teacherId']);
        $userName = $details['userName'];
        $teacherMail = $details['teacherMail'];
        $assignment = $details['assignment'];

        $emailBody = "Õpilane <strong>{$userName}</strong> on esitanud lahenduse ülesandele <strong>{$assignment['assignmentName']}</strong>.<br>";
        $emailBody .= "Lahenduse link: <a href='{$solutionUrl}'>{$solutionUrl}</a><br>";

        Mail::send(
            $teacherMail,
            $assignment['subjectName'] . ": $userName on esitanud lahenduse ülesandele " . $assignment['assignmentName'],
            $emailBody
        );

        stop(200, 'Solution url saved');
    }

    function ajax_saveStudentCriteria()
    {
        $this->auth->userIsAdmin || $this->auth->userId == $_POST['studentId'] || stop(403, 'Permission denied');
        $studentId = $_POST['studentId'];
        $criteria = $_POST['criteria'];

        $isSaveAllowed = $this->checkIfStudentHasAllCriteria();
        if (!$isSaveAllowed) {
            stop(403, 'Save not allowed');
        }

        $this->saveStudentCriteria();

        stop(200, 'Criteria saved');
    }

    function ajax_saveMessage()
    {
        $this->auth->userIsAdmin || $this->auth->userId == $_POST['userId'] || stop(403, 'Permission denied');
        $assignmentId = $_POST['assignmentId'];
        $userId = $_POST['userId'];
        $content = $_POST['content'];

        $this->saveMessage($assignmentId, $userId, $content);
        if ($userId != $_POST['teacherId']) {
            $details = $this->getAssignmentDetails($assignmentId, $userId, $_POST['teacherId']);
            $userName = $details['userName'];
            $teacherMail = $details['teacherMail'];
            $assignment = $details['assignment'];

            $emailBody = "Õpilane <strong>{$userName}</strong> saatis sõnumi ülesande <strong>{$assignment['assignmentName']}</strong> kohta.<br>";
            $emailBody .= "Sõnumi sisu: <br>" . nl2br($content) . "<br><br>";
            $url= BASE_URL . 'assignments/' . $assignmentId;
            $emailBody .= "Ülesande link: <a href='{$url}'>{$url}</a><br>";

            Mail::send(
                $teacherMail,
                $assignment['subjectName'] . ": $userName saatis sõnumi ülesande " . $assignment['assignmentName'] . " kohta",
                $emailBody
            );
        }

        stop(200, 'Message saved');
    }

    function ajax_editAssignment()
    {
        $this->auth->userIsAdmin || $this->auth->userId == $_POST['teacherId'] || stop(403, 'Permission denied');
        $assignmentId = $_POST['assignmentId'];
        $assignmentName = $_POST['assignmentName'];
        $assignmentInstructions = $_POST['assignmentInstructions'];
        $assignmentDueAt = $_POST['assignmentDueAt'];
        $oldCriteria = $_POST['oldCriteria'] ?? [];
        $newCriteria = $_POST['newCriteria'] ?? [];

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

        $this->saveEditAssignmentCriteria($oldCriteria, $newCriteria, $assignmentId);

        Db::update('assignments', ['assignmentName' => $assignmentName, 'assignmentInstructions' => $assignmentInstructions, 'assignmentDueAt' => $assignmentDueAt], 'assignmentId = ?', [$assignmentId]);

        stop(200, 'Assignment edited');

    }

    function saveNewCriterion()
    {
        $this->auth->userIsAdmin || $this->auth->userId == $_POST['teacherId'] || stop(403, 'Permission denied');
        $assignmentId = $_POST['assignmentId'];
        $criterionName = $_POST['criterionName'];

        $existCriterion = Db::getOne('SELECT criterionId FROM criteria WHERE assignmentId = ? AND criterionName = ?', [$assignmentId, $criterionName]);
        if ($existCriterion) {
            stop(400, 'Criterion already exists');
        }

        $criterionId = Db::insert('criteria', ['assignmentId' => $assignmentId, 'criterionName' => $criterionName]);

        $message = "$_POST[teacherName] lisas kriteeriumi '$criterionName'.";
        $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);

        stop(200, ['criterionId' => $criterionId]);
    }

    private function saveStudentCriteria()
    {
        $studentId = $_POST['studentId'];
        $criteria = $_POST['criteria'];
        foreach ($criteria as $criterionId => $completed) {
            $existCriterion = Db::getOne('SELECT criterionId FROM userDoneCriteria WHERE userId = ? AND criterionId = ?', [$studentId, $criterionId]);
            $criterionName = Db::getOne('SELECT criterionName FROM criteria WHERE criterionId = ?', [$criterionId]);
            if (!$existCriterion && $completed === 'true') {
                Db::insert('userDoneCriteria', ['userId' => $studentId, 'criterionId' => $criterionId]);
                if ($this->auth->userIsAdmin || $this->auth->userId == $_POST['teacherId']) {
                    $message = "$_POST[teacherName] märkis õpilasele $_POST[studentName] kriteeriumi '$criterionName' tehtuks.";;
                    $this->saveMessage($_POST['assignmentId'], $_POST['teacherId'], $message, true);
                }
            } elseif ($existCriterion && $completed === 'false') {
                Db::delete('userDoneCriteria', 'userId = ? AND criterionId = ?', [$studentId, $criterionId]);
                if ($this->auth->userIsAdmin || $this->auth->userId == $_POST['teacherId']) {
                    $message = "$_POST[teacherName] märkis õpilasele $_POST[studentName] kriteeriumi '$criterionName' mittetehtuks.";;
                    $this->saveMessage($_POST['assignmentId'], $_POST['teacherId'], $message, true);
                }

            }
        }
    }

    private function getAssignmentDetails($assignmentId, $studentId, $teacherId)
    {
        $userName = Db::getOne('SELECT userName FROM users WHERE userId = ?', [$studentId]);
        $teacherMail = Db::getOne('SELECT userEmail FROM users WHERE userId = ?', [$teacherId]);
        $assignment = Db::getFirst('
        SELECT a.assignmentName, s.subjectName
        FROM assignments a
        JOIN subjects s ON a.subjectId = s.subjectId
        WHERE a.assignmentId = ?', [$assignmentId]);

        return [
            'userName' => $userName,
            'teacherMail' => $teacherMail,
            'assignment' => $assignment
        ];
    }

    private function saveEditAssignmentCriteria($oldCriteria, $newCriteria, $assignmentId)
    {
        //Get all criteria for this assignment
        $criteria = Db::getAll('SELECT criterionId, criterionName FROM criteria WHERE assignmentId = ?', [$assignmentId]);

        if (count($oldCriteria) > 0) {
            foreach ($criteria as $criterion) {
                if (!array_key_exists($criterion['criterionId'], $oldCriteria)) {
                    $message = "$_POST[teacherName] eemaldas kriteeriumi '$criterion[criterionName]'.";
                    $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
                    Db::delete('userDoneCriteria', 'criterionId = ?', [$criterion['criterionId']]);
                    Db::delete('criteria', 'criterionId = ?', [$criterion['criterionId']]);
                }
            }
        }

        if (count($newCriteria) > 0) {
            foreach ($newCriteria as $criterionName) {
                Db::insert('criteria', ['assignmentId' => $assignmentId, 'criterionName' => $criterionName]);
                $message = "$_POST[teacherName] lisas uue kriteeriumi '$criterionName'.";
                $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
            }
        }
    }

    private function checkIfStudentHasAllCriteria()
    {
        $criteria = $_POST['criteria'];
        if (count($criteria) > 0) {
            $falseCriteria = array_keys(array_filter($criteria, function ($value) {
                return $value === 'false';
            }));

            return count($falseCriteria) === 0;
        }
        return false;
    }

    private function saveMessage($assignmentId, $userId, $content, $isNotification = false)
    {
        Db::insert('messages', ['assignmentId' => $assignmentId, 'userId' => $userId, 'content' => $content, 'CreatedAt' => date('Y-m-d H:i:s'), 'isNotification' => $isNotification]);
    }
}
