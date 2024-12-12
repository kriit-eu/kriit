<?php namespace App;

class assignments extends Controller
{
    public $template = 'master';

    public function view(): void
    {
        if (!$this->checkIfUserHasPermissionForAction($this->getId())) {
            $this->redirect('subjects');
        }

        $assignmentId = $this->getId();
        $userId = $this->auth->userId;
        $isTeacher = \App\Assignment::userIsTeacher($userId, $assignmentId) || $this->auth->userIsAdmin;
        $isStudent = !$isTeacher;
        $this->action = $isTeacher ? 'teacher_view' : 'student_view';
        $this->template = $this->auth->userIsAdmin ? 'admin' : 'master';

        $params = [$assignmentId];
        $query = "
            SELECT
                a.assignmentId, a.assignmentName, a.assignmentInstructions, a.assignmentDueAt,
                u.userId AS studentId, u.userName AS studentName, u.groupId,
                ua.userGrade, ua.assignmentStatusId, ua.solutionUrl,
                ast.statusName AS assignmentStatusName,
                subj.teacherId AS teacherId,
                t.userName AS teacherName,
                JSON_ARRAYAGG(
                    DISTINCT
                    JSON_OBJECT(
                        'criterionId', c.criterionId,
                        'criterionName', c.criterionName,
                        'completed', udc.criterionId IS NOT NULL
                    )
                ) AS criteria,
                JSON_ARRAYAGG(
                    DISTINCT
                    IF(ac.assignmentCommentId IS NOT NULL,
                        JSON_OBJECT(
                            'id', ac.assignmentCommentId,
                            'comment', ac.assignmentComment,
                            'createdAt', DATE_FORMAT(ac.assignmentCommentCreatedAt, '%d.%m.%Y %H:%i'),
                            'name', uc.userName
                        ),
                        NULL
                    )
                ) AS comments
            FROM assignments a
            LEFT JOIN criteria c ON c.assignmentId = a.assignmentId
            JOIN subjects subj ON a.subjectId = subj.subjectId
            JOIN users t ON subj.teacherId = t.userId
            LEFT JOIN groups g ON subj.groupId = g.groupId
            LEFT JOIN users u ON u.groupId = g.groupId
            LEFT JOIN userAssignments ua ON ua.assignmentId = a.assignmentId AND ua.userId = u.userId
            LEFT JOIN assignmentStatuses ast ON ua.assignmentStatusId = ast.assignmentStatusId
            LEFT JOIN userDoneCriteria udc ON udc.criterionId = c.criterionId AND udc.userId = u.userId
            LEFT JOIN assignmentComments ac ON ac.assignmentId = a.assignmentId AND ac.studentId = u.userId
            LEFT JOIN users uc ON ac.studentId = uc.userId
            WHERE a.assignmentId = ?
        ";

        if ($isStudent) {
            $query .= " AND u.userId = ?";
            $params[] = $userId;
        }
        $query .= " GROUP BY a.assignmentId, u.userId";

        $data = Db::getAll($query, $params);

        $assignment = [
            'assignmentId' => $assignmentId,
            'criteria' => Db::getAll("SELECT * FROM criteria WHERE assignmentId = ?", [$assignmentId]),
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
            if (!isset($assignment['students'][$studentId])) {
                $grade = trim($row['userGrade'] ?? '');
                $statusName = $row['assignmentStatusName'] ?? 'Esitamata';

                // Decode JSON arrays
                $criteria = json_decode($row['criteria'], true) ?: [];
                $comments = json_decode($row['comments'], true) ?: [];

                // Filter out null values from comments
                $comments = array_filter($comments, fn($comment) => !is_null($comment['id']));

                // Calculate criteria completion
                $userDoneCriteriaCount = count(array_filter($criteria, fn($c) => $c['completed']));
                $isAllCriteriaCompleted = count($criteria) > 0 && $userDoneCriteriaCount === count($criteria);

                $assignment['students'][$studentId] = [
                    'studentId' => $studentId,
                    'studentName' => $row['studentName'],
                    'grade' => $grade,
                    'assignmentStatusName' => $statusName,
                    'initials' => mb_substr($row['studentName'], 0, 1) . mb_substr($row['studentName'], mb_strrpos($row['studentName'], ' ') + 1, 1),
                    'solutionUrl' => trim($row['solutionUrl'] ?? ''),
                    'comments' => $comments,
                    'userDoneCriteria' => array_column($criteria, null, 'criterionId'),
                    'userDoneCriteriaCount' => $userDoneCriteriaCount,
                    'isAllCriteriaCompleted' => $isAllCriteriaCompleted,
                    'studentActionButtonName' => !empty(trim($row['solutionUrl'] ?? '')) ? 'Muuda' : 'Esita'
                ];


            }
        }

        $this->assignment = $assignment;
    }


    function ajax_checkCriterionNameSize()
    {
        $criterionName = $_POST['criterionName'];

        $lengthInBytes = mb_strlen($criterionName, '8bit');

        $maxBytes = (int)Db::getOne("
            SELECT CHARACTER_MAXIMUM_LENGTH
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = 'criteria'
            AND COLUMN_NAME = 'criterionName'
        ");

        if ($lengthInBytes > $maxBytes) {
            stop(400, 'Kriteeriumi nimi on liiga pikk');
        }
        stop(200, 'OK');
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


    function saveStudentComment(): void
    {
        $assignmentId = $_POST['assignmentId'];
        $this->checkIfUserHasPermissionForAction($assignmentId) || stop(403, 'Teil pole õigusi selle tegevusele.');

        $studentId = $_POST['studentId'];
        $comment = $_POST['comment'];

        $this->addAssignmentCommentForStudent($studentId, $assignmentId, $comment, $_POST['teacherName']);

        $this->saveMessage($assignmentId, $_POST['teacherId'], "$_POST[teacherName] lisas õpilasele $_POST[studentName] tagasisideks: '$comment'", true);

        stop(200, 'Comment saved');
    }

    function ajax_saveStudentSolutionUrl(): void
    {
        $assignmentId = $_POST['assignmentId'];
        $this->checkIfUserHasPermissionForAction($assignmentId) || stop(403, 'Teil pole õigusi sellele tegevusele.');
        $studentId = $_POST['studentId'];
        $solutionUrl = $_POST['solutionUrl'];
        $commentForTeacher = $_POST['comment'];

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


        $existAssignment = Db::getFirst('SELECT * FROM userAssignments JOIN users USING(userId) WHERE userId = ? AND assignmentId = ? ', [$studentId, $assignmentId]);
        if (!$existAssignment) {
            Db::insert('userAssignments', ['userId' => $studentId, 'assignmentId' => $assignmentId, 'assignmentStatusId' => 2, 'solutionUrl' => $solutionUrl]);
            Activity::create(ACTIVITY_SUBMIT_ASSIGNMENT, $this->auth->userId, $assignmentId, "esitas ülesande lahenduse");
            $studentName = Db::getOne('SELECT userName FROM users WHERE userId = ?', [$studentId]);
        } else {
            Db::update('userAssignments', ['assignmentStatusId' => 2, 'solutionUrl' => $solutionUrl], 'userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
            Activity::create(ACTIVITY_SUBMIT_ASSIGNMENT, $this->auth->userId, $assignmentId, "esitas ülesande lahenduse uuesti");
            $studentName = $existAssignment['userName'];
        }


        if ($commentForTeacher) {

            $this->addAssignmentCommentForStudent(
                $studentId,
                $assignmentId,
                $commentForTeacher,
                $studentName);

            $this->saveMessage(
                $assignmentId,
                $studentId,
                "$_POST[studentName] lisas kommentaari: '$commentForTeacher'",
                true);
        }

        $mailData = $this->getSenderNameAndReceiverEmail($studentId, $_POST['teacherId']);
        $studentName = $mailData['senderName'];
        $teacherMail = $mailData['receiverMail'];
        $assignment = $this->getAssignmentDetails($assignmentId);

        $emailBody = $existAssignment && $existAssignment['userGrade'] && ($existAssignment['userGrade'] === 'MA' || is_numeric(intval($existAssignment['userGrade']) < 3)) ?
            sprintf(
                "Õpilane <strong>%s</strong> parandas ülesande '<a href=\"" . BASE_URL . "assignments/" . $assignmentId . "\"><strong>%s</strong></a> lahendust.<br><br>Lahenduse link: <a href='%s'>%s</a><br>",
                $studentName,
                $assignment['assignmentName'],
                $solutionUrl,
                $solutionUrl
            ) :
            sprintf(
                "Õpilane <strong>%s</strong> esitas lahenduse ülesandele '<a href=\"" . BASE_URL . "assignments/" . $assignmentId . "\"><strong>%s</strong></a>'.<br><br>Lahenduse link: <a href='%s'>%s</a><br>", $studentName,
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
        $assignmentDueAt = empty($_POST['assignmentDueAt']) ? null : $_POST['assignmentDueAt'];
        $oldCriteria = $_POST['oldCriteria'] ?? [];
        $newCriteria = $_POST['newCriteria'] ?? [];

        $existAssignment = Db::getFirst('SELECT * FROM assignments WHERE assignmentId = ?', [$assignmentId]);
        $this->saveEditAssignmentCriteria($oldCriteria, $newCriteria, $assignmentId);

        Db::update('assignments', ['assignmentName' => $assignmentName, 'assignmentInstructions' => $assignmentInstructions, 'assignmentDueAt' => $assignmentDueAt], 'assignmentId = ?', [$assignmentId]);

        if ($existAssignment['assignmentName'] !== $assignmentName) {
            $message = "$_POST[teacherName] muutis ülesande nimeks '$assignmentName'.";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
            Activity::create(ACTIVITY_UPDATE_ASSIGNMENT, $this->auth->userId, $assignmentId, "Changed assignment from '$existAssignment[assignmentName]' to '$assignmentName'");
        }
        if ($existAssignment['assignmentInstructions'] !== $assignmentInstructions) {
            $message = "$_POST[teacherName] muutis ülesande juhendiks '$assignmentInstructions'.";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
            Activity::create(ACTIVITY_UPDATE_ASSIGNMENT, $this->auth->userId, $assignmentId, "Changed assignment instructions from '$existAssignment[assignmentInstructions]' to '$assignmentInstructions'");
        }

        if ($existAssignment['assignmentDueAt'] !== $assignmentDueAt) {
            $date = date('d.m.Y', strtotime($assignmentDueAt));
            $message = "$_POST[teacherName] muutis ülesande tähtajaks '$date'.";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
            Activity::create(ACTIVITY_UPDATE_ASSIGNMENT, $this->auth->userId, $assignmentId, "Changed assignment due date from '$existAssignment[assignmentDueAt]' to '$assignmentDueAt'");
        }

        stop(200, 'Assignment edited');

    }

    function ajax_validateAndCheckLinkAccessibility(): void
    {
        $solutionUrl = $_POST['solutionUrl'];

        $response = $this->validateSolutionUrl($solutionUrl);

        stop($response['code'], $response['message']);
    }


    private function saveOrUpdateUserAssignment($studentId, $assignmentId, $grade, $comment): bool
    {
        $existUserAssignment = Db::getFirst('SELECT * FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
        $isUpdated = false;

        if (!$existUserAssignment) {
            Db::insert('userAssignments', ['userId' => $studentId, 'assignmentId' => $assignmentId, 'userGrade' => $grade, 'assignmentStatusId' => $grade ? 3 : 1, 'comments' => '[]']);
            $message = "$_POST[teacherName] lisas õpilasele $_POST[studentName] hindeks: $grade";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
        } else {
            Db::update('userAssignments', [
                'userGrade' => $grade,
                'assignmentStatusId' => $grade ? 3 : $existUserAssignment['assignmentStatusId']
            ], 'userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);

            if ($existUserAssignment['userGrade'] !== $grade) {
                $this->saveMessage(
                    $assignmentId,
                    $_POST['teacherId'],
                    "$_POST[teacherName] muutis õpilase $_POST[studentName] hinnet: $existUserAssignment[userGrade] -> $grade",
                    true);
            }

            $isUpdated = true;
        }

        if ($comment) {

            $this->addAssignmentCommentForStudent(
                $studentId,
                $assignmentId,
                $comment,
                $this->auth->userName);

            $this->saveMessage(
                $assignmentId,
                $_POST['teacherId'],
                "$_POST[teacherName] lisas õpilase $_POST[studentName] lahenduse tagasisideks: '$comment'",
                true);
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
            $assignment['assignmentDueAt'] = !empty ($assignment['assignmentDueAt']) ? date('d.m.Y', strtotime($row['assignmentDueAt'])) : null;
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
        if (!isset($_POST['criteria']) || empty($_POST['criteria'])) {
            return true;
        }

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

        $parsedUrl = parse_url($solutionUrl);
        $host = $parsedUrl['host'] ?? '';
        $path = $parsedUrl['path'] ?? '';


        if ($host === 'github.com') {
            $githubCommitUrl = '/\/commit\/[0-9a-fA-F]{40}/';
            $githubRepoUrl = '/\/[a-zA-Z0-9-]+\/[a-zA-Z0-9-]+/';
            $githubIssuesUrl = '/.*\/issues/';
            if (preg_match($githubCommitUrl, $path) !== 1
                && preg_match($githubRepoUrl, $path) !== 1
                && preg_match($githubIssuesUrl, $path) !== 1
            ) {
                return ['code' => 400, 'message' => 'GitHubi URL peab olema kas commiti, repositooriumi või issue link.'];
            }
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

        if (!$data) {
            return false;
        }

        $data['groupIds'] = explode(',', $data['groupIds']);

        return ($this->auth->userIsAdmin || $this->auth->userId == $data['teacherId'] ||
            $this->auth->groupId && in_array((string)$this->auth->groupId, $data['groupIds']));

    }

    /**
     * @param $studentId
     * @param $assignmentId
     * @param $comment
     * @return void
     */
    public function addAssignmentCommentForStudent($studentId, $assignmentId, $comment, $commentAuthorName): void
    {
        $existingComments = Db::getOne('SELECT comments FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
        $comments = $existingComments ? json_decode($existingComments, true) : [];

        $comments[] = [
            'name' => $commentAuthorName,
            'comment' => trim($comment),
            'createdAt' => date('Y-m-d H:i:s')
        ];

        Db::update('userAssignments', ['comments' => json_encode($comments)], 'userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
    }

}
