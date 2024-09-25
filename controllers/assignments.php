<?php namespace App;

class assignments extends Controller
{
    public $template = 'master';

    public function view(): void
    {
        $this->checkIfUserHasPermissionForAction($this->getId()) || $this->redirect('subjects');

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
                LEFT JOIN criteria c ON c.assignmentId = a.assignmentId
                JOIN subjects subj ON a.subjectId = subj.subjectId
                JOIN users t ON subj.teacherId = t.userId
                LEFT JOIN groups g ON subj.groupId = g.groupId
                LEFT JOIN users u ON u.groupId = g.groupId
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
                $assignment['assignmentDueAt'] = !empty($row['assignmentDueAt']) ? date('d.m.Y', strtotime($row['assignmentDueAt'])) : 'Pole määratud';
                $assignment['teacherId'] = $row['teacherId'];
                $assignment['teacherName'] = $row['teacherName'];
            }

            $studentId = $row['studentId'];
            $criteriaId = $row['criterionId'];

            if ($this->isStudent && $this->auth->userId !== $studentId) {
                continue;
            }

            if (!empty($criteriaId)) {
                if (!isset($assignment['criteria'][$criteriaId])) {
                    $assignment['criteria'][$criteriaId] = [
                        'criteriaId' => $criteriaId,
                        'criteriaName' => $row['criterionName']
                    ];
                }
            }

            if (!isset($assignment['students'][$studentId])) {
                $assignment['students'][$studentId] = [
                    'studentId' => $studentId,
                    'studentName' => $row['studentName'],
                    'grade' => !empty($row['userGrade']) ? trim($row['userGrade']) : '',
                    'assignmentStatusName' => $row['assignmentStatusName'] ?? 'Esitamata',
                    'initials' => isset($row['studentName']) ? mb_substr($row['studentName'], 0, 1) . mb_substr($row['studentName'], mb_strrpos($row['studentName'], ' ') + 1, 1) : '',
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

            $class = '';

            $daysRemaining = null;
            if (!empty($row['assignmentDueAt'])) {
                $daysRemaining = (int)(new \DateTime())->diff(new \DateTime($row['assignmentDueAt']))->format('%r%a');
                $class = $daysRemaining <= 0 ?
                    (($this->isStudent && $statusName == 'Esitamata') ||
                    ($this->isStudent && $isLowGrade) ||
                    ($this->isTeacher && $statusName !== 'Hinnatud') ? 'red-cell' : '') :
                    ($isLowGrade ? 'red-cell' : ($statusClassMap[$statusName] ?? ''));
            }

            $tooltipText = $statusName . (($daysRemaining !== null && $daysRemaining < 0 && $statusName === 'Esitamata') ? ' (Tähtaeg möödas)' : '');
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


    function ajax_saveAssignmentGrade(): void
    {
        $assignmentId = $_POST['assignmentId'];
        $this->checkIfUserHasPermissionForAction($assignmentId) || stop(403, 'Teil pole õigusi sellele tegevusele.');

        $studentId = $_POST['studentId'];
        $grade = $_POST['grade'] ?? null;
        $criteria = $_POST['criteria'] ?? [];
        $comment = $_POST['comment'] ?? null;

        if ($grade !== null && !in_array($grade, ['1', '2', '3', '4', '5', 'A', 'MA'])) {
            stop(400, 'Invalid grade');
        }

        if (count($criteria) !== 0) {
            $falseCriteria = array_keys(array_filter($criteria, fn($value) => $value === 'false'));
            if (count($falseCriteria) > 0) {
                $grade = 'MA';
            }
            $this->saveStudentCriteria();
        }

        if ($comment) {
            $existUserAssignment = Db::getFirst('SELECT * FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);

            if ($existUserAssignment && !$existUserAssignment['comment']) {
                $message = "$_POST[teacherName] lisas õpilasele $_POST[studentName] tagasisideks: '$comment'";
                $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
            } elseif ($existUserAssignment && $existUserAssignment['comment'] !== $comment) {
                $message = "$_POST[teacherName] muutis õpilase $_POST[studentName] tagasisidet: '$existUserAssignment[comment]' -> '$comment'";
                $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
            }

        }

        $isUpdated = $this->saveOrUpdateUserAssignment($studentId, $assignmentId, $grade, $comment);
        $this->sendGradeNotification($assignmentId, $studentId, $_POST['teacherId'], $isUpdated);

        stop(200, 'Grade saved');
    }

    function ajax_saveStudentSolutionUrl(): void
    {
        $assignmentId = $_POST['assignmentId'];
        $this->checkIfUserHasPermissionForAction($assignmentId) || stop(403, 'Teil pole õigusi sellele tegevusele.');
        $studentId = $_POST['studentId'];
        $solutionUrl = $_POST['solutionUrl'];

        $ifStudentHasAllCriteria = $this->checkIfStudentHasAllCriteria();
        $ifStudentHasPositiveGrade = $this->checkIfStudentHasPositiveGrade($studentId, $assignmentId);
        $isValidUrl = $this->validateSolutionUrl($solutionUrl);

        if (!$ifStudentHasAllCriteria) {
            error_out('Teil pole täidetud kõiki kriteeriume.', 403);
        }

        if ($ifStudentHasPositiveGrade) {
            error_out('Teil on juba positiivne hinne.', 403);
        }

        if ($isValidUrl['code'] !== 200) {
            error_out($isValidUrl['message'], 400);
        }

        $this->saveStudentCriteria();

        $existAssignment = Db::getFirst('SELECT * FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
        if (!$existAssignment) {
            Db::insert('userAssignments', ['userId' => $studentId, 'assignmentId' => $assignmentId, 'assignmentStatusId' => 2, 'solutionUrl' => $solutionUrl]);
        } else {
            Db::update('userAssignments', ['assignmentStatusId' => 2, 'solutionUrl' => $solutionUrl], 'userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
        }

        $mailData = $this->getSenderNameAndReceiverEmail($studentId, $_POST['teacherId']);
        $studentName = $mailData['senderName'];
        $teacherMail = $mailData['receiverMail'];
        $assignment = $this->getAssignmentDetails($assignmentId);

        $emailBody = $existAssignment && $existAssignment['userGrade'] && ($existAssignment['userGrade'] === 'MA' || is_numeric(intval($existAssignment['userGrade']) < 3)) ?
            sprintf(
                "Õpilane <strong>%s</strong> parandas ülesande '<strong>%s</strong>' lahendust.<br><br>Lahenduse link: <a href='%s'>%s</a><br>",
                $studentName,
                $assignment['assignmentName'],
                $solutionUrl,
                $solutionUrl
            ) :
            sprintf(
                "Õpilane <strong>%s</strong> esitas lahenduse ülesandele '<strong>%s</strong>'.<br><br>Lahenduse link: <a href='%s'>%s</a><br>",
                $studentName,
                $assignment['assignmentName'],
                $solutionUrl,
                $solutionUrl
            );

        $subject = $existAssignment && $existAssignment['userGrade'] && ($existAssignment['userGrade'] === 'MA' || is_numeric(intval($existAssignment['userGrade']) < 3)) ?
            $assignment['subjectName'] . ": $studentName parandas ülesande '" . $assignment['assignmentName'] . "' lahendust" :
            $assignment['subjectName'] . ": $studentName esitas lahenduse ülesandele '" . $assignment['assignmentName'] . "'";

        if ($teacherMail) {
            $this->sendNotificationToEmail($teacherMail, $subject, $emailBody);
        }

        stop(200, 'Solution url saved');
    }

    function ajax_saveStudentCriteria(): void
    {
        $assignmentId = $_POST['assignmentId'];
        $this->checkIfUserHasPermissionForAction($assignmentId) || stop(403, 'Teil pole õigusi sellele tegevusele.');

        $this->saveStudentCriteria();

        stop(200, 'Criteria saved');
    }

    function ajax_saveMessage(): void
    {
        $assignmentId = $_POST['assignmentId'];
        $this->checkIfUserHasPermissionForAction($assignmentId) || stop(403, 'Teil pole õigusi sellele tegevusele.');
        $content = $_POST['content'];

        $answerToId = $_POST['answerToId'] ?? null;

        $this->saveMessage($assignmentId, $_POST['userId'], $content);

        $receiverId = $answerToId ? Db::getOne('SELECT userId FROM messages WHERE messageId = ?', [$answerToId]) : $_POST['teacherId'];
        $mailData = $this->getSenderNameAndReceiverEmail($_POST['userId'], $receiverId);

        $senderName = $mailData['senderName'];
        $receiverMail = $mailData['receiverMail'];
        $assignment = $this->getAssignmentDetails($assignmentId);


        $emailBody = $answerToId ? sprintf(
            "<strong>%s</strong> vastas teie sõnumile ülesande '<strong>%s</strong>' kohta.<br>Vastus: <br>%s<br><br>Ülesande link: <a href='%s'>%s</a><br>",
            $senderName,
            $assignment['assignmentName'],
            nl2br($content),
            BASE_URL . 'assignments/' . $assignmentId,
            BASE_URL . 'assignments/' . $assignmentId
        ) : sprintf(
            "<strong>%s</strong> saatis sõnumi ülesande '<strong>%s</strong>' kohta.<br>Sõnum: <br>%s<br><br>Ülesande link: <a href='%s'>%s</a><br>",
            $senderName,
            $assignment['assignmentName'],
            nl2br($content),
            BASE_URL . 'assignments/' . $assignmentId,
            BASE_URL . 'assignments/' . $assignmentId
        );

        $subject = $answerToId ?
            $assignment['subjectName'] . ": $senderName vastas teie sõnumile ülesande '" . $assignment['assignmentName'] . "' kohta" :
            $assignment['subjectName'] . ": $senderName saatis teile sõnumi ülesande '" . $assignment['assignmentName'] . "' kohta";

        if ($receiverMail) {
            $this->sendNotificationToEmail($receiverMail, $subject, $emailBody);
        }

        stop(200, 'Message saved');
    }

    function ajax_editAssignment(): void
    {
        $assignmentId = $_POST['assignmentId'];
        $this->checkIfUserHasPermissionForAction($assignmentId) || stop(403, 'Teil pole õigusi sellele tegevusele.');

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

    function ajax_validateAndCheckLinkAccessibility(): void
    {
        $solutionUrl = $_POST['solutionUrl'];

        $response = $this->validateSolutionUrl($solutionUrl);

        stop($response['code'], $response['message']);
    }

    private function saveStudentCriteria(): void
    {
        $studentId = $_POST['studentId'];
        $criteria = $_POST['criteria'];

        foreach ($criteria as $criterionId => $completed) {
            $existCriterion = Db::getOne('SELECT criterionId FROM userDoneCriteria WHERE userId = ? AND criterionId = ?', [$studentId, $criterionId]);
            $criterionName = Db::getOne('SELECT criterionName FROM criteria WHERE criterionId = ?', [$criterionId]);
            if (!$existCriterion && $completed === 'true') {
                Db::insert('userDoneCriteria', ['userId' => $studentId, 'criterionId' => $criterionId]);
                if ($this->auth->userIsAdmin || $this->auth->userId == $_POST['teacherId']) {
                    $message = "$_POST[teacherName] märkis õpilasele $_POST[studentName] kriteeriumi '$criterionName' tehtuks.";
                    $this->saveMessage($_POST['assignmentId'], $_POST['teacherId'], $message, true);
                }
            } elseif ($existCriterion && $completed === 'false') {
                Db::delete('userDoneCriteria', 'userId = ? AND criterionId = ?', [$studentId, $criterionId]);
                if ($this->auth->userIsAdmin || $this->auth->userId == $_POST['teacherId']) {
                    $message = "$_POST[teacherName] märkis õpilasele $_POST[studentName] kriteeriumi '$criterionName' mittetehtuks.";
                    $this->saveMessage($_POST['assignmentId'], $_POST['teacherId'], $message, true);
                }

            }
        }
    }

    private function saveOrUpdateUserAssignment($studentId, $assignmentId, $grade, $comment): bool
    {
        $existUserAssignment = Db::getFirst('SELECT * FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
        $isUpdated = false;

        if (!$existUserAssignment) {
            Db::insert('userAssignments', ['userId' => $studentId, 'assignmentId' => $assignmentId, 'userGrade' => $grade, 'assignmentStatusId' => $grade ? 3 : 1, 'comment' => $comment]);
            $message = "$_POST[teacherName] lisas õpilasele $_POST[studentName] hindeks: $grade";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
        } else {
            Db::update('userAssignments', ['userGrade' => $grade, 'assignmentStatusId' => 3, 'comment' => $comment], 'userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
            $message = "$_POST[teacherName] muutis õpilase $_POST[studentName] hinnet: $existUserAssignment[userGrade] -> $grade";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
            $isUpdated = !empty($existUserAssignment['userGrade']);
        }

        return $isUpdated;
    }

    private function sendGradeNotification($assignmentId, $studentId, $teacherId, $isUpdated): void
    {
        $mailData = $this->getSenderNameAndReceiverEmail($teacherId, $studentId);
        $teacherName = $mailData['senderName'];
        $studentMail = $mailData['receiverMail'];
        $assignment = $this->getAssignmentDetails($assignmentId);

        $emailBody = sprintf(
            "<strong>%s</strong> hindas %s teie esitatud lahendust ülesandele '<strong>%s</strong>'.<br><br>Ülesande link: <a href='%s'>%s</a><br>",
            $teacherName,
            $isUpdated ? 'uuesti' : '',
            $assignment['assignmentName'],
            BASE_URL . 'assignments/' . $assignmentId,
            BASE_URL . 'assignments/' . $assignmentId
        );

        $subject = sprintf(
            "%s: %s hindas %s teie esitatud lahendust ülesandele '%s'",
            $assignment['subjectName'],
            $teacherName,
            $isUpdated ? 'uuesti' : '',
            $assignment['assignmentName']
        );

        if ($studentMail) {
            $this->sendNotificationToEmail($studentMail, $subject, $emailBody);
        }
    }

    private function getAssignmentDetails($assignmentId): array
    {
        $data = Db::getAll("
            SELECT
                a.assignmentId,
                a.assignmentName,
                a.assignmentDueAt,
                subj.subjectId,
                subj.subjectName,
                subj.teacherId,
                t.userName AS teacherName,
                GROUP_CONCAT(DISTINCT g.groupId) AS groupIds
            FROM assignments a
            JOIN subjects subj ON a.subjectId = subj.subjectId
            JOIN users t ON subj.teacherId = t.userId
            JOIN groups g ON subj.groupId = g.groupId
            WHERE a.assignmentId = ?
            GROUP BY a.assignmentId, a.assignmentName, a.assignmentDueAt, subj.subjectId, subj.subjectName, subj.teacherId, t.userName, t.userEmail
        ", [$assignmentId]);

        $assignment = [
            'assignmentId' => $assignmentId,
            'assignmentName' => null,
            'assignmentDueAt' => null,
            'teacherId' => null,
            'teacherName' => null,
            'subjectId' => null,
            'subjectName' => null,
            'groupIds' => [],
        ];

        if (!empty($data)) {
            $row = $data[0];

            $assignment['assignmentName'] = $row['assignmentName'];
            $assignment['assignmentDueAt'] = date('d.m.Y', strtotime($row['assignmentDueAt']));
            $assignment['teacherId'] = $row['teacherId'];
            $assignment['teacherName'] = $row['teacherName'];
            $assignment['subjectId'] = $row['subjectId'];
            $assignment['subjectName'] = $row['subjectName'];

            $assignment['groupIds'] = explode(',', $row['groupIds']);
        }

        return $assignment;
    }

    private function getSenderNameAndReceiverEmail($senderId, $receiverId): array
    {
        $result = Db::getFirst('
        SELECT s.userName AS senderName, r.userEmail AS receiverMail
        FROM users s
        JOIN users r ON r.userId = ?
        WHERE s.userId = ?
    ', [$receiverId, $senderId]);

        return ['senderName' => $result['senderName'], 'receiverMail' => $result['receiverMail']];
    }

    private function saveEditAssignmentCriteria($oldCriteria, $newCriteria, $assignmentId): void
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


    private function checkIfStudentHasAllCriteria(): bool
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

    private function validateSolutionUrl($solutionUrl): array
    {
        if (!filter_var($solutionUrl, FILTER_VALIDATE_URL)) {
            return ['code' => 400, 'message' => 'Sisestatud link pole kehtiv. Palun sisestage kehtiv link.'];
        }

        $headers = @get_headers($solutionUrl);
        if ($headers && strpos($headers[0], '200')) {
            return ['code' => 200, 'message' => 'Link on kättesaadav'];

        } else {
            return ['code' => 400, 'message' => 'Sisestatud link pole kättesaadav. Kontrollige, kas see on privaatne või vale link.'];
        }

    }

    private function checkIfStudentHasPositiveGrade($studentId, $assignmentId): bool
    {
        $userGrade = Db::getOne('SELECT userGrade FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);

        return !empty($userGrade) &&
            $userGrade !== 'MA' &&
            (is_numeric($userGrade) && intval($userGrade) > 2);
    }

    private function saveMessage($assignmentId, $userId, $content, $isNotification = false): void
    {
        Db::insert('messages', ['assignmentId' => $assignmentId, 'userId' => $userId, 'content' => $content, 'CreatedAt' => date('Y-m-d H:i:s'), 'isNotification' => $isNotification]);

    }

    private function sendNotificationToEmail($receiverEmail, $subject, $content): void
    {
        Mail::send(
            $receiverEmail,
            $subject,
            $content
        );
    }

    private function checkIfUserHasPermissionForAction($assignmentId): bool
    {
        $data = Db::getFirst('
            SELECT subj.subjectId, subj.teacherId, GROUP_CONCAT(subj.groupId) AS groupIds
            FROM assignments a
            JOIN subjects subj ON a.subjectId = subj.subjectId
            WHERE a.assignmentId = ?
            GROUP BY subj.subjectId, subj.teacherId
        ', [$assignmentId]);

        if (!$data){
            return false;
        }

        $data['groupIds'] = explode(',', $data['groupIds']);

        return ($this->auth->userIsAdmin || $this->auth->userId == $data['teacherId'] ||
            $this->auth->groupId && in_array((string)$this->auth->groupId, $data['groupIds']));

    }

}