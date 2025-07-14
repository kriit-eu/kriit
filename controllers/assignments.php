<?php

namespace App;

use Parsedown;

class assignments extends Controller
{
    public function ajax_getAssignmentCriteria()
    {
        $assignmentId = $_GET['assignmentId'] ?? null;
        if (!$assignmentId) {
            stop(400, 'Missing assignmentId');
        }
        // Permission check (reuse existing logic)
        $this->checkIfUserHasPermissionForAction($assignmentId) || stop(403, 'Teil pole õigusi sellele tegevusele.');
        $criteria = Db::getAll('SELECT criterionId, criterionName FROM criteria WHERE assignmentId = ?', [$assignmentId]);
        stop(200, ['criteria' => $criteria]);
    }
    public $template = 'master';

    public function view(): void
    {
        $this->template = $this->auth->userIsAdmin ? 'admin' : 'master';

        $this->checkIfUserHasPermissionForAction($this->getId()) || $this->redirect('subjects');

        $this->isStudent = $this->auth->groupId && !$this->auth->userIsAdmin && !$this->auth->userIsTeacher;
        $this->isTeacher = $this->auth->userIsTeacher;
        $assignmentId = $this->getId();

        // Build WHERE clause for student filtering based on user permissions
        $studentFilterClause = '';
        if ($this->isStudent) {
            // Students only see themselves
            $studentFilterClause = " AND u.userId = {$this->auth->userId}";
        } elseif ($this->isTeacher && !$this->auth->userIsAdmin) {
            // For teachers: show all students who have userAssignments for this assignment
            // regardless of group - this allows cross-group assignments to work properly
            $studentFilterClause = "";
        } elseif ($this->auth->userIsAdmin) {
            // For admins: show all students who have userAssignments for this assignment
            // regardless of group - this allows cross-group assignments to work properly
            $studentFilterClause = "";
        }

        $data = Db::getAll("
                SELECT
                    a.assignmentId, a.assignmentName, a.assignmentInstructions, a.assignmentDueAt, a.assignmentInvolvesOpenApi,
                    c.criterionId, c.criterionName,
                    u.userId AS studentId, u.userName AS studentName, u.groupId, g.groupName,
                    ua.userGrade, ua.assignmentStatusId, ua.solutionUrl, ua.comments,
                    ast.statusName AS assignmentStatusName,
                    udc.criterionId AS userDoneCriterionId,
                    subj.teacherId AS teacherId,
                    t.userName AS teacherName
                FROM assignments a
                LEFT JOIN criteria c ON c.assignmentId = a.assignmentId
                JOIN subjects subj ON a.subjectId = subj.subjectId
                JOIN users t ON subj.teacherId = t.userId
                INNER JOIN userAssignments ua ON ua.assignmentId = a.assignmentId
                INNER JOIN users u ON ua.userId = u.userId
                LEFT JOIN groups g ON u.groupId = g.groupId /* Use student's actual group */
                LEFT JOIN assignmentStatuses ast ON ua.assignmentStatusId = ast.assignmentStatusId
                LEFT JOIN userDoneCriteria udc ON udc.criterionId = c.criterionId AND udc.userId = u.userId
                WHERE a.assignmentId = ? {$studentFilterClause} AND u.userDeleted = 0 AND u.userIsActive = 1
                ORDER BY g.groupName, u.userName, c.criterionId
            ", [$assignmentId]);

        $assignment = [
            'assignmentId' => $assignmentId,
            'assignmentName' => null,
            'assignmentInstructions' => null,
            'assignmentDueAt' => null,
            'assignmentInvolvesOpenApi' => 0,
            'teacherId' => null,
            'teacherName' => null,
            'criteria' => [],
            'students' => [],
            'messages' => []
        ];

        // Determine the primary group for this assignment
        $groupParam = $_GET['group'] ?? null; // Get group from URL parameter
        $primaryGroupId = $this->determinePrimaryGroup($data, $groupParam);
        $primaryGroupName = $this->getPrimaryGroupName($data, $primaryGroupId);

        $parsedown = Parsedown::instance();

        foreach ($data as $row) {
            if (empty($assignment['assignmentName'])) {
                $assignment['assignmentName'] = $row['assignmentName'];
                $assignment['assignmentInstructions'] = $row['assignmentInstructions'];
                $assignment['assignmentDueAt'] = !empty($row['assignmentDueAt']) ? date('d.m.Y', strtotime($row['assignmentDueAt'])) : 'Pole tähtaega';
                $assignment['assignmentInvolvesOpenApi'] = (int)$row['assignmentInvolvesOpenApi'];
                $assignment['teacherId'] = $row['teacherId'];
                $assignment['teacherName'] = $row['teacherName'];
                $assignment['primaryGroupName'] = $primaryGroupName;
            }

            $studentId = $row['studentId'];
            $criteriaId = $row['criterionId'];

            if ($this->isStudent && $this->auth->userId !== $studentId) {
                continue;
            }

            // Filter students to only show those from the primary group
            if ($primaryGroupId && $row['groupId'] != $primaryGroupId) {
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
                $comments = json_decode($row['comments'] ?? '[]', true) ?? [];

                // Note: Comments are processed client-side with parseMarkdown function
                // No server-side Parsedown processing to avoid double-processing issues

                $assignment['students'][$studentId] = [
                    'studentId' => $studentId,
                    'studentName' => $row['studentName'],
                    'grade' => !empty($row['userGrade']) ? trim($row['userGrade']) : '',
                    'assignmentStatusName' => $row['assignmentStatusName'] ?? 'Esitamata',
                    'initials' => isset($row['studentName']) ? mb_substr($row['studentName'], 0, 1) . mb_substr($row['studentName'], mb_strrpos($row['studentName'], ' ') + 1, 1) : '',
                    'solutionUrl' => isset($row['solutionUrl']) ? trim($row['solutionUrl']) : '',
                    'comments' => $comments,
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
            $statusId = $row['assignmentStatusId'] ?? ASSIGNMENT_STATUS_NOT_SUBMITTED;
            $grade = !empty($row['userGrade']) ? trim($row['userGrade']) : '';
            $isNegativeGrade = $grade == 'MA' || (is_numeric($grade) && intval($grade) < 3);
            $isEvaluated = isset($row['assignmentStatusName']) && $row['assignmentStatusName'] === 'Hinnatud';

            $isAllCriteriaCompleted = true;
            foreach ($assignment['students'][$studentId]['userDoneCriteria'] as $criterion) {
                if (!$criterion['completed']) {
                    $isAllCriteriaCompleted = false;
                    break;
                }
            }

            $assignment['students'][$studentId]['isDisabledStudentActionButton'] =
                (($isEvaluated && !$isNegativeGrade) || (!$isEvaluated && !$isAllCriteriaCompleted)) ? 'disabled' : '';

            $assignment['students'][$studentId]['isAllCriteriaCompleted'] = $isAllCriteriaCompleted;

            $dueDate = !empty($row['assignmentDueAt']) ? new \DateTime($row['assignmentDueAt']) : null;
            $daysRemaining = $dueDate ? (int)(new \DateTime())->diff($dueDate)->format('%r%a') : 1000;

            $class = Assignment::cellColor(
                $this->isStudent,
                $this->isTeacher,
                $isNegativeGrade,
                $daysRemaining,
                $statusId,
                $statusName
            );

            $tooltipText = $statusName . (($daysRemaining < 0 && $statusName === 'Esitamata') ? ' (Tähtaeg möödas)' : '');
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
            // Note: Message content is processed client-side to avoid double-processing issues
            // Store raw content for client-side processing
            $content = $message['messageContent'];

            $assignment['messages'][] = [
                'messageId' => $message['messageId'],
                'content' => $content,
                'userId' => $message['messageUserId'],
                'userName' => $message['messageUserName'],
                'createdAt' => $this->formatMessageDate($message['CreatedAt']),
                'isNotification' => $message['isNotification']
            ];
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
            $this->saveStudentCriteria();
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
        $commentForTeacher = $_POST['comment'];

        $subjectId = Db::getOne("SELECT subjectId FROM assignments WHERE assignmentId = ?", [$assignmentId]);
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

        // Use Assignment class to submit solution
        Assignment::submitSolution($assignmentId, $studentId, $solutionUrl);

        if (!$existAssignment) {
            Activity::create(ACTIVITY_SUBMIT_ASSIGNMENT, $this->auth->userId, $assignmentId, "esitas ülesande lahenduse");
            $studentName = Db::getOne('SELECT userName FROM users WHERE userId = ?', [$studentId]);
        } else {
            Activity::create(ACTIVITY_SUBMIT_ASSIGNMENT, $this->auth->userId, $assignmentId, "esitas ülesande lahenduse uuesti");
            $studentName = $existAssignment['userName'];
        }

        // Set subject as not synchronized if the assignment is not synchronized
        Db::update('subjects', ['isSynchronized' => 0], 'subjectId = ?', [$subjectId]);

        if ($commentForTeacher) {

            $this->addAssignmentCommentForStudent(
                $studentId,
                $assignmentId,
                $commentForTeacher,
                $studentName
            );

            $this->saveMessage(
                $assignmentId,
                $studentId,
                "$_POST[studentName] lisas kommentaari: '$commentForTeacher'",
                true
            );
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
                "Õpilane <strong>%s</strong> esitas lahenduse ülesandele '<a href=\"" . BASE_URL . "assignments/" . $assignmentId . "\"><strong>%s</strong></a>'.<br><br>Lahenduse link: <a href='%s'>%s</a><br>",
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
        $assignmentDueAt = empty($_POST['assignmentDueAt']) ? null : $_POST['assignmentDueAt'];
        $assignmentInvolvesOpenApi = isset($_POST['assignmentInvolvesOpenApi']) ? (int)$_POST['assignmentInvolvesOpenApi'] : 0;
        $oldCriteria = $_POST['oldCriteria'] ?? [];
        $newCriteria = $_POST['newCriteria'] ?? [];

        $existAssignment = Db::getFirst('SELECT * FROM assignments WHERE assignmentId = ?', [$assignmentId]);
        $this->saveEditAssignmentCriteria($oldCriteria, $newCriteria, $assignmentId);

        Db::update('assignments', [
            'assignmentName' => $assignmentName,
            'assignmentInstructions' => $assignmentInstructions,
            'assignmentDueAt' => $assignmentDueAt,
            'assignmentInvolvesOpenApi' => $assignmentInvolvesOpenApi
        ], 'assignmentId = ?', [$assignmentId]);

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

        if ((int)$existAssignment['assignmentInvolvesOpenApi'] !== $assignmentInvolvesOpenApi) {
            $status = $assignmentInvolvesOpenApi ? 'aktiveeris' : 'deaktiveeris';
            $message = "$_POST[teacherName] $status OpenAPI toe ülesandel.";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
            Activity::create(ACTIVITY_UPDATE_ASSIGNMENT, $this->auth->userId, $assignmentId, "Changed assignment OpenAPI support from '" . (int)$existAssignment['assignmentInvolvesOpenApi'] . "' to '$assignmentInvolvesOpenApi'");
        }

        stop(200, 'Assignment edited');
    }

    function ajax_validateAndCheckLinkAccessibility(): void
    {
        $solutionUrl = $_POST['solutionUrl'];

        $response = $this->validateSolutionUrl($solutionUrl);

        stop($response['code'], $response['message']);
    }

    function ajax_getOpenApiPrompt(): void
    {
        // Get the OpenAPI prompt from settings
        $prompt = Db::getOne("SELECT settingValue FROM settings WHERE settingName = 'openapiPrompt'");

        // If the prompt doesn't exist, return an empty string
        if ($prompt === false) {
            $prompt = '';
        }

        stop(200, ['prompt' => $prompt]);
    }

    function ajax_saveOpenApiPrompt(): void
    {
        // Only admins can save the prompt
        if (!$this->auth->userIsAdmin) {
            stop(403, 'Only admins can save the OpenAPI prompt');
        }

        $prompt = $_POST['prompt'] ?? '';

        // Check if the setting already exists
        $existingSetting = Db::getOne("SELECT settingName FROM settings WHERE settingName = 'openapiPrompt'");

        if ($existingSetting) {
            // Update the existing setting
            Db::update('settings', ['settingValue' => $prompt], "settingName = 'openapiPrompt'");
        } else {
            // Create a new setting
            Db::insert('settings', [
                'settingName' => 'openapiPrompt',
                'settingValue' => $prompt
            ]);
        }

        stop(200, 'Prompt saved successfully');
    }

    function ajax_fetchSwaggerDoc(): void
    {
        $url = $_POST['url'] ?? '';

        if (empty($url)) {
            stop(400, 'URL is required');
        }

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            stop(400, 'Invalid URL format');
        }

        try {
            // Fetch the swagger-ui-init.js file
            $jsContent = @file_get_contents($url);

            if ($jsContent === false) {
                stop(404, 'Failed to fetch the OpenAPI specification file');
            }

            // Extract the swaggerDoc object using a simpler approach
            // First, find the position of "swaggerDoc"
            $pos = stripos($jsContent, '"swaggerDoc"');
            if ($pos === false) {
                $pos = stripos($jsContent, "'swaggerDoc'");
            }

            if ($pos !== false) {
                // Find the opening brace after "swaggerDoc":
                $openBracePos = strpos($jsContent, '{', $pos);
                if ($openBracePos !== false) {
                    // Now we need to find the matching closing brace
                    $braceCount = 1;
                    $currentPos = $openBracePos + 1;
                    $endPos = null;

                    // Simple brace counting to find the matching closing brace
                    while ($braceCount > 0 && $currentPos < strlen($jsContent)) {
                        $char = $jsContent[$currentPos];
                        if ($char === '{') {
                            $braceCount++;
                        } elseif ($char === '}') {
                            $braceCount--;
                            if ($braceCount === 0) {
                                $endPos = $currentPos;
                                break;
                            }
                        }
                        $currentPos++;
                    }

                    if ($endPos !== null) {
                        // Extract the JSON object including the braces
                        $swaggerDocJson = substr($jsContent, $openBracePos, $endPos - $openBracePos + 1);

                        // Parse the JSON
                        $swaggerDoc = json_decode($swaggerDocJson, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            // Try to clean the JSON string before parsing
                            // Replace JavaScript-style trailing commas
                            $cleanedJson = preg_replace('/,\s*\}/', '}', $swaggerDocJson);
                            // Replace single quotes with double quotes for JSON compatibility
                            $cleanedJson = preg_replace('/([{,])\s*\'([^\']*)\'\'\s*:/', '$1"$2":', $cleanedJson);
                            $cleanedJson = preg_replace('/:\s*\'([^\']*)\'\'/', ':"$1"', $cleanedJson);

                            $swaggerDoc = json_decode($cleanedJson, true);

                            if (json_last_error() !== JSON_ERROR_NONE) {
                                stop(500, 'Failed to parse OpenAPI specification: ' . json_last_error_msg());
                            }
                        }

                        // Return the parsed swaggerDoc
                        stop(200, ['swaggerDoc' => $swaggerDoc]);
                    }
                }
            }

            // If we get here, we couldn't extract the swaggerDoc using the brace-counting approach
            // Try a simpler regex approach for the options object
            if (preg_match('/var\s+options\s*=\s*\{([^;]*)\};/s', $jsContent, $matches)) {
                $optionsStr = '{' . $matches[1] . '}';

                // Try to extract swaggerDoc using a simple regex
                if (preg_match('/["\']swaggerDoc["\']\s*:\s*\{/s', $optionsStr, $docMatches, PREG_OFFSET_CAPTURE)) {
                    $docStart = $docMatches[0][1];
                    $docStartBrace = strpos($optionsStr, '{', $docStart + strlen($docMatches[0][0]) - 1);

                    // Count braces to find the end of the swaggerDoc object
                    $braceCount = 1;
                    $currentPos = $docStartBrace + 1;
                    $endPos = null;

                    while ($braceCount > 0 && $currentPos < strlen($optionsStr)) {
                        $char = $optionsStr[$currentPos];
                        if ($char === '{') {
                            $braceCount++;
                        } elseif ($char === '}') {
                            $braceCount--;
                            if ($braceCount === 0) {
                                $endPos = $currentPos;
                                break;
                            }
                        }
                        $currentPos++;
                    }

                    if ($endPos !== null) {
                        $swaggerDocJson = substr($optionsStr, $docStartBrace, $endPos - $docStartBrace + 1);
                        $swaggerDoc = json_decode($swaggerDocJson, true);

                        if (json_last_error() === JSON_ERROR_NONE) {
                            stop(200, null, ['swaggerDoc' => $swaggerDoc]);
                        }
                    }
                }

                // If we still couldn't extract it, try to convert the entire options object to JSON
                $optionsJson = preg_replace('/([{,])\s*([a-zA-Z0-9_]+)\s*:/s', '$1"$2":', $optionsStr);
                $options = json_decode($optionsJson, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($options['swaggerDoc'])) {
                    stop(200, ['swaggerDoc' => $options['swaggerDoc']]);
                }
            }

            // If all approaches failed
            stop(404, 'Could not find or parse swaggerDoc in the JavaScript file');
        } catch (\Exception $e) {
            stop(500, 'An error occurred: ' . $e->getMessage());
        }
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
        $currentTime = date('Y-m-d H:i:s');

        if (!$existUserAssignment) {
            Db::insert('userAssignments', [
                'userId' => $studentId,
                'assignmentId' => $assignmentId,
                'userGrade' => $grade,
                'assignmentStatusId' => $grade ? 3 : 1,
                'comments' => '[]',
                'userAssignmentGradedAt' => $grade ? $currentTime : null
            ]);
            $message = "$_POST[teacherName] lisas õpilasele $_POST[studentName] hindeks: $grade";
            $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
        } else {
            $updateData = [
                'userGrade' => $grade,
                'assignmentStatusId' => $grade ? 3 : $existUserAssignment['assignmentStatusId']
            ];

            // Only set gradedAt if there's a grade and it's different from before
            if ($grade && $existUserAssignment['userGrade'] !== $grade) {
                $updateData['userAssignmentGradedAt'] = $currentTime;
            }

            Db::update('userAssignments', $updateData, 'userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);

            if ($existUserAssignment['userGrade'] !== $grade) {

                // Get subject ID from assignment ID
                $subjectId = Db::getOne("SELECT subjectId FROM assignments WHERE assignmentId = ?", [$assignmentId]);

                // Set subject as not synchronized
                Db::update('subjects', ['isSynchronized' => 0], 'subjectId = ?', [$subjectId]);

                $this->saveMessage(
                    $assignmentId,
                    $_POST['teacherId'],
                    "$_POST[teacherName] muutis õpilase $_POST[studentName] hinnet: $existUserAssignment[userGrade] -> $grade",
                    true
                );
            }

            $isUpdated = true;
        }

        if ($comment) {

            $this->addAssignmentCommentForStudent(
                $studentId,
                $assignmentId,
                $comment,
                $this->auth->userName
            );

            $this->saveMessage(
                $assignmentId,
                $_POST['teacherId'],
                "$_POST[teacherName] lisas õpilase $_POST[studentName] lahenduse tagasisideks: '$comment'",
                true
            );
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
            $assignment['assignmentDueAt'] = !empty($assignment['assignmentDueAt']) ? date('d.m.Y', strtotime($row['assignmentDueAt'])) : null;
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
            foreach ($newCriteria as $crit) {
                // Accept both string and array/object
                if (is_array($crit) && isset($crit['criteriaName'])) {
                    $criterionName = $crit['criteriaName'];
                } else {
                    $criterionName = $crit;
                }
                if (!empty($criterionName)) {
                    Db::insert('criteria', ['assignmentId' => $assignmentId, 'criterionName' => $criterionName]);
                    $message = "$_POST[teacherName] lisas uue kriteeriumi '$criterionName'.";
                    $this->saveMessage($assignmentId, $_POST['teacherId'], $message, true);
                }
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
            if (
                preg_match($githubCommitUrl, $path) !== 1
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
     * @param $commentAuthorName
     * @return void
     */
    public function addAssignmentCommentForStudent($studentId, $assignmentId, $comment, $commentAuthorName): void
    {
        $existingComments = Db::getOne('SELECT comments FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
        $comments = $existingComments ? json_decode($existingComments, true) : [];
        $currentTime = date('Y-m-d H:i:s');

        $comments[] = [
            'name' => $commentAuthorName,
            'comment' => trim($comment),
            'createdAt' => $currentTime
        ];

        // Use the Assignment class for updating comments
        Db::update('userAssignments', [
            'comments' => json_encode($comments)
        ], 'userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);

        // Note: In the future, we should refactor this to use:
        // Assignment::addComment($assignmentId, $studentId, trim($comment), $commentAuthorName);
        // But this would require modifying the Assignment::addComment method to also accept the author name
    }

    /**
     * Determine the primary group for an assignment based on navigation context or student distribution
     * 
     * @param array $data Query result data
     * @param string|null $groupParam Group name from URL parameter (navigation context)
     * @return int|null Primary group ID or null if no groups found
     */
    private function determinePrimaryGroup(array $data, ?string $groupParam = null): ?int
    {
        if (empty($data)) {
            return null;
        }

        // If user is a student, use their group as primary
        if ($this->isStudent) {
            return $this->auth->groupId;
        }

        // Priority 1: Use group from navigation context (URL parameter) if available
        if ($groupParam) {
            foreach ($data as $row) {
                if ($row['groupName'] === $groupParam) {
                    return $row['groupId'];
                }
            }
        }

        // Priority 2: Count students per group and return the one with most students (fallback)
        $groupCounts = [];
        foreach ($data as $row) {
            $groupId = $row['groupId'];
            if ($groupId) {
                if (!isset($groupCounts[$groupId])) {
                    $groupCounts[$groupId] = 0;
                }
                $groupCounts[$groupId]++;
            }
        }

        // Return the group with the most students
        if (empty($groupCounts)) {
            return null;
        }

        return array_key_first(array_filter($groupCounts, function ($count) use ($groupCounts) {
            return $count === max($groupCounts);
        }));
    }

    /**
     * Get the primary group name for display
     * 
     * @param array $data Query result data
     * @param int|null $primaryGroupId Primary group ID
     * @return string|null Primary group name or null if not found
     */
    private function getPrimaryGroupName(array $data, ?int $primaryGroupId): ?string
    {
        if (!$primaryGroupId) {
            return null;
        }

        foreach ($data as $row) {
            if ($row['groupId'] == $primaryGroupId) {
                return $row['groupName'];
            }
        }

        return null;
    }
}
