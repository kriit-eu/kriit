<?php namespace App;

/**
 * Class grading
 * Controller for the grading view
 */
class grading extends Controller
{
    public $template = 'admin';

    /**
     * This is the default function that will be called when this controller is invoked
     */
    public function index(): void
    {
        // Check if user is a teacher
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            $this->redirect('');
            exit();
        }

        // Fetch all submissions with comment counts and criteria
        $submissions = Db::getAll("
            SELECT
                ua.userAssignmentSubmittedAt AS 'Esitatud',
                CASE
                    WHEN ua.userGrade IS NOT NULL AND ua.userGrade != '' THEN ua.userAssignmentGradedAt
                    ELSE NULL
                END AS 'Hinnatud',
                u.userName AS 'Õpilane',
                s.subjectName AS 'Aine',
                a.assignmentName AS 'Ülesanne',
                s.subjectId,
                ua.userId,
                ua.assignmentId,
                ua.solutionUrl,
                ua.comments,
                ua.userGrade,
                a.assignmentInstructions,
                (
                    SELECT COUNT(m.messageId)
                    FROM messages m
                    LEFT JOIN users mu ON m.userId = mu.userId
                    WHERE m.assignmentId = ua.assignmentId
                    AND m.isNotification = 0
                    AND (m.userId = ua.userId OR mu.userIsTeacher = 1 OR mu.userIsAdmin = 1)
                ) AS commentCount,
                (
                    SELECT GROUP_CONCAT(
                        JSON_OBJECT(
                            'criterionId', c.criterionId,
                            'criterionName', c.criterionName,
                            'isCompleted', IF(udc.criterionId IS NOT NULL, true, false)
                        )
                        SEPARATOR ','
                    )
                    FROM criteria c
                    LEFT JOIN userDoneCriteria udc ON c.criterionId = udc.criterionId AND ua.userId = udc.userId
                    WHERE c.assignmentId = ua.assignmentId
                ) AS criteriaJson
            FROM userAssignments ua
            LEFT JOIN users u ON ua.userId = u.userId
            LEFT JOIN assignments a ON ua.assignmentId = a.assignmentId
            LEFT JOIN subjects s ON a.subjectId = s.subjectId
            WHERE assignmentStatusId = 2
            ORDER BY ua.userAssignmentSubmittedAt ASC
        ");

        // Calculate days ago for each submission and process criteria
        foreach ($submissions as &$submission) {
            // Calculate days since submission (Vanus)
            if ($submission['Esitatud']) {
                $submissionDate = new \DateTime($submission['Esitatud']);
                $today = new \DateTime();
                $today->setTime(0, 0, 0); // Set to start of day for accurate comparison
                $submissionDate->setTime(0, 0, 0); // Set to start of day for accurate comparison

                $daysAgo = $today->diff($submissionDate)->days;
                $submission['Vanus'] = $daysAgo == 0 ? '' : $daysAgo;
            } else {
                $submission['Vanus'] = '';
            }

            // Calculate difference between submission and grading (Vahe)
            if ($submission['Esitatud'] && $submission['Hinnatud']) {
                $submissionDate = new \DateTime($submission['Esitatud']);
                $gradingDate = new \DateTime($submission['Hinnatud']);
                $submissionDate->setTime(0, 0, 0);
                $gradingDate->setTime(0, 0, 0);

                $daysDiff = $gradingDate->diff($submissionDate)->days;
                $submission['Vahe'] = $daysDiff == 0 ? '' : $daysDiff;
            } else {
                $submission['Vahe'] = '';
            }

            // Process criteria JSON
            if ($submission['criteriaJson']) {
                // The GROUP_CONCAT creates a comma-separated list of JSON objects, so we need to wrap it in an array
                $criteriaArray = '[' . $submission['criteriaJson'] . ']';
                $submission['criteria'] = $criteriaArray;
            } else {
                $submission['criteria'] = '[]';
            }
        }

        // Get distinct subjects and assign rainbow colors
        $distinctSubjects = [];
        foreach ($submissions as $submission) {
            if (!isset($distinctSubjects[$submission['subjectId']])) {
                $distinctSubjects[$submission['subjectId']] = $submission['Aine'];
            }
        }

        // Create color mapping for subjects (assign colors sequentially)
        $subjectColors = [];
        $colorIndex = 0;
        foreach ($distinctSubjects as $subjectId => $subjectName) {
            $subjectColors[$subjectId] = $colorIndex % 24; // We have 24 colors (0-23)
            $colorIndex++;
        }

        // Set view variables
        $this->submissions = $submissions;
        $this->subjectColors = $subjectColors;
    }

    /**
     * AJAX method to fetch messages for a specific assignment and student
     */
    public function getMessages(): void
    {
        // Check if user is a teacher
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            stop(403, 'Access denied');
        }

        $assignmentId = $_POST['assignmentId'] ?? null;
        $studentId = $_POST['studentId'] ?? null;

        if (!$assignmentId) {
            stop(400, 'Assignment ID required');
        }

        if (!$studentId) {
            stop(400, 'Student ID required');
        }

        // Fetch messages for this assignment and student
        // Include messages from the specific student and from teachers (who have userIsTeacher = 1)
        $messages = Db::getAll("
            SELECT
                m.messageId,
                m.content,
                m.userId,
                m.CreatedAt,
                m.isNotification,
                u.userName,
                u.userIsTeacher
            FROM messages m
            LEFT JOIN users u ON m.userId = u.userId
            WHERE m.assignmentId = ?
            AND (m.userId = ? OR u.userIsTeacher = 1 OR u.userIsAdmin = 1)
            ORDER BY m.CreatedAt ASC
        ", [$assignmentId, $studentId]);

        // Format messages
        $formattedMessages = [];
        foreach ($messages as $message) {
            $formattedMessages[] = [
                'messageId' => $message['messageId'],
                'content' => $message['content'],
                'userId' => $message['userId'],
                'userName' => $message['userName'],
                'createdAt' => $message['CreatedAt'],
                'isNotification' => $message['isNotification']
            ];
        }

        stop(200, $formattedMessages);
    }

    /**
     * AJAX method to save a new message
     */
    public function saveMessage(): void
    {
        // Check if user is a teacher
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            stop(403, 'Access denied');
        }

        $assignmentId = $_POST['assignmentId'] ?? null;
        $content = $_POST['content'] ?? null;

        if (!$assignmentId || !$content) {
            stop(400, 'Assignment ID and content required');
        }

        // Get assignment and teacher information for logging
        $assignmentInfo = Db::getFirst("SELECT assignmentName, subjectId FROM assignments WHERE assignmentId = ?", [$assignmentId]);
        $teacherInfo = Db::getFirst("SELECT userName FROM users WHERE userId = ?", [$this->auth->userId]);

        // Save the message (teacher's message in the context of this assignment)
        Db::insert('messages', [
            'assignmentId' => $assignmentId,
            'userId' => $this->auth->userId,
            'content' => trim($content),
            'CreatedAt' => date('Y-m-d H:i:s'),
            'isNotification' => 0
        ]);

        // Log the activity
        Activity::create(ACTIVITY_TEACHER_ADD_COMMENT, $this->auth->userId, $assignmentId, [
            'assignmentName' => $assignmentInfo['assignmentName'] ?? 'Unknown',
            'teacherName' => $teacherInfo['userName'] ?? 'Unknown',
            'commentLength' => strlen(trim($content)),
            'action' => 'added_comment'
        ]);

        stop(200, 'Message saved');
    }

    /**
     * AJAX method to save grade and comment
     */
    public function saveGrade(): void
    {
        // Check if user is a teacher
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            stop(403, 'Access denied');
        }

        $assignmentId = $_POST['assignmentId'] ?? null;
        $studentId = $_POST['studentId'] ?? null;
        $grade = $_POST['grade'] ?? null;
        $comment = $_POST['comment'] ?? null;
        $criteria = $_POST['criteria'] ?? [];

        if (!$assignmentId || !$studentId) {
            stop(400, 'Assignment ID and student ID required');
        }

        if ($grade !== null && !in_array($grade, ['2', '3', '4', '5', 'A', 'MA'])) {
            stop(400, 'Invalid grade');
        }

        // Get teacher and student information for notifications
        $teacherInfo = Db::getFirst("SELECT userId, userName FROM users WHERE userId = ?", [$this->auth->userId]);
        $studentInfo = Db::getFirst("SELECT userId, userName FROM users WHERE userId = ?", [$studentId]);
        $assignmentInfo = Db::getFirst("SELECT assignmentName FROM assignments WHERE assignmentId = ?", [$assignmentId]);

        if (!$teacherInfo || !$studentInfo || !$assignmentInfo) {
            stop(400, 'Invalid assignment, teacher, or student');
        }

        // Save criteria if provided
        if (!empty($criteria)) {
            $this->saveStudentCriteria($studentId, $assignmentId, $criteria, $teacherInfo, $studentInfo);
        }

        // Save or update the grade
        $isUpdated = $this->saveOrUpdateUserAssignment($studentId, $assignmentId, $grade, $comment, $teacherInfo, $studentInfo);

        // Send notification email to student
        $this->sendGradeNotification($assignmentId, $studentId, $teacherInfo, $isUpdated);

        stop(200, 'Grade saved');
    }

    /**
     * Save or update user assignment with grade
     */
    private function saveOrUpdateUserAssignment($studentId, $assignmentId, $grade, $comment, $teacherInfo, $studentInfo): bool
    {
        $existUserAssignment = Db::getFirst('SELECT * FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
        $isUpdated = false;
        $currentTime = date('Y-m-d H:i:s');

        if (!$existUserAssignment) {
            Db::insert('userAssignments', [
                'userId' => $studentId,
                'assignmentId' => $assignmentId,
                'userGrade' => $grade,
                'assignmentStatusId' => $grade ? 3 : 1, // 3 = Graded, 1 = Submitted
                'comments' => '[]',
                'userAssignmentGradedAt' => $grade ? $currentTime : null
            ]);

            if ($grade) {
                $message = "$teacherInfo[userName] lisas õpilasele $studentInfo[userName] hindeks: $grade";
                $this->saveMessageInternal($assignmentId, $teacherInfo['userId'], $message, true);

                // Log grading activity
                $assignmentInfo = Db::getFirst("SELECT assignmentName, subjectId FROM assignments WHERE assignmentId = ?", [$assignmentId]);
                Activity::create(ACTIVITY_TEACHER_GRADE_ASSIGNMENT, $teacherInfo['userId'], $assignmentId, [
                    'assignmentName' => $assignmentInfo['assignmentName'] ?? 'Unknown',
                    'teacherName' => $teacherInfo['userName'],
                    'studentName' => $studentInfo['userName'],
                    'studentId' => $studentId,
                    'grade' => $grade,
                    'action' => 'new_grade',
                    'subjectId' => $assignmentInfo['subjectId'] ?? null
                ]);
            }
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
                $assignmentInfo = Db::getFirst("SELECT subjectId, assignmentName FROM assignments WHERE assignmentId = ?", [$assignmentId]);
                $subjectId = $assignmentInfo['subjectId'];

                // Set subject as not synchronized
                Db::update('subjects', ['isSynchronized' => 0], 'subjectId = ?', [$subjectId]);

                $oldGrade = $existUserAssignment['userGrade'] ?: 'puudub';
                $message = "$teacherInfo[userName] muutis õpilase $studentInfo[userName] hinnet: $oldGrade -> $grade";
                $this->saveMessageInternal($assignmentId, $teacherInfo['userId'], $message, true);

                // Log grade update activity
                Activity::create(ACTIVITY_TEACHER_GRADE_ASSIGNMENT, $teacherInfo['userId'], $assignmentId, [
                    'assignmentName' => $assignmentInfo['assignmentName'] ?? 'Unknown',
                    'teacherName' => $teacherInfo['userName'],
                    'studentName' => $studentInfo['userName'],
                    'studentId' => $studentId,
                    'oldGrade' => $oldGrade,
                    'newGrade' => $grade,
                    'action' => 'updated_grade',
                    'subjectId' => $subjectId
                ]);
            }

            $isUpdated = true;
        }

        // Save teacher comment if provided
        if ($comment && trim($comment)) {
            $this->saveMessageInternal($assignmentId, $teacherInfo['userId'], trim($comment), false);

            // Log comment activity
            $assignmentInfo = Db::getFirst("SELECT assignmentName, subjectId FROM assignments WHERE assignmentId = ?", [$assignmentId]);
            Activity::create(ACTIVITY_TEACHER_ADD_COMMENT, $teacherInfo['userId'], $assignmentId, [
                'assignmentName' => $assignmentInfo['assignmentName'] ?? 'Unknown',
                'teacherName' => $teacherInfo['userName'],
                'studentName' => $studentInfo['userName'],
                'studentId' => $studentId,
                'commentLength' => strlen(trim($comment)),
                'action' => 'added_comment_with_grade',
                'subjectId' => $assignmentInfo['subjectId'] ?? null
            ]);
        }

        return $isUpdated;
    }

    /**
     * Send grade notification email to student
     */
    private function sendGradeNotification($assignmentId, $studentId, $teacherInfo, $isUpdated): void
    {
        $studentInfo = Db::getFirst("SELECT userEmail FROM users WHERE userId = ?", [$studentId]);
        $assignmentInfo = Db::getFirst("SELECT assignmentName FROM assignments WHERE assignmentId = ?", [$assignmentId]);

        if (!$studentInfo['userEmail'] || !$assignmentInfo) {
            return; // Skip if no email or assignment not found
        }

        $emailBody = sprintf(
            "<strong>%s</strong> hindas %s teie esitatud lahendust ülesandele '<strong>%s</strong>'.<br><br>Ülesande link: <a href='%s'>%s</a><br>",
            $teacherInfo['userName'],
            $isUpdated ? 'uuesti' : '',
            $assignmentInfo['assignmentName'],
            BASE_URL . 'assignments/' . $assignmentId,
            BASE_URL . 'assignments/' . $assignmentId
        );

        $this->sendNotificationToEmail($studentInfo['userEmail'],
            "Teie lahendus on hinnatud",
            $emailBody);

        // Log email notification activity
        Activity::create(ACTIVITY_TEACHER_SEND_EMAIL, $teacherInfo['userId'], $assignmentId, [
            'assignmentName' => $assignmentInfo['assignmentName'],
            'teacherName' => $teacherInfo['userName'],
            'studentEmail' => $studentInfo['userEmail'],
            'studentId' => $studentId,
            'emailSubject' => "Teie lahendus on hinnatud",
            'action' => $isUpdated ? 'grade_updated_email' : 'grade_assigned_email'
        ]);
    }

    /**
     * Save message internally
     */
    private function saveMessageInternal($assignmentId, $userId, $content, $isNotification = false): void
    {
        Db::insert('messages', [
            'assignmentId' => $assignmentId,
            'userId' => $userId,
            'content' => $content,
            'CreatedAt' => date('Y-m-d H:i:s'),
            'isNotification' => $isNotification
        ]);
    }

    /**
     * Send notification email
     */
    private function sendNotificationToEmail($receiverEmail, $subject, $content): void
    {
        Mail::send($receiverEmail, $subject, $content);
    }

    /**
     * Save student criteria completion status
     */
    private function saveStudentCriteria($studentId, $assignmentId, $criteria, $teacherInfo, $studentInfo): void
    {
        $criteriaChanges = [];

        foreach ($criteria as $criterionId => $completed) {
            $existCriterion = Db::getFirst('SELECT * FROM userDoneCriteria WHERE userId = ? AND criterionId = ?', [$studentId, $criterionId]);
            $criterionName = Db::getOne('SELECT criterionName FROM criteria WHERE criterionId = ?', [$criterionId]);

            if (!$existCriterion && $completed === 'true') {
                Db::insert('userDoneCriteria', ['userId' => $studentId, 'criterionId' => $criterionId]);
                if ($this->auth->userIsAdmin || $this->auth->userId == $teacherInfo['userId']) {
                    $message = "$teacherInfo[userName] märkis õpilasele $studentInfo[userName] kriteeriumi '$criterionName' tehtuks.";
                    $this->saveMessageInternal($assignmentId, $teacherInfo['userId'], $message, true);

                    $criteriaChanges[] = [
                        'criterionId' => $criterionId,
                        'criterionName' => $criterionName,
                        'action' => 'marked_completed'
                    ];
                }
            } elseif ($existCriterion && $completed === 'false') {
                Db::delete('userDoneCriteria', 'userId = ? AND criterionId = ?', [$studentId, $criterionId]);
                if ($this->auth->userIsAdmin || $this->auth->userId == $teacherInfo['userId']) {
                    $message = "$teacherInfo[userName] märkis õpilasele $studentInfo[userName] kriteeriumi '$criterionName' mittetehtuks.";
                    $this->saveMessageInternal($assignmentId, $teacherInfo['userId'], $message, true);

                    $criteriaChanges[] = [
                        'criterionId' => $criterionId,
                        'criterionName' => $criterionName,
                        'action' => 'marked_incomplete'
                    ];
                }
            }
        }

        // Log criteria update activity if there were changes
        if (!empty($criteriaChanges)) {
            $assignmentInfo = Db::getFirst("SELECT assignmentName, subjectId FROM assignments WHERE assignmentId = ?", [$assignmentId]);
            Activity::create(ACTIVITY_TEACHER_UPDATE_CRITERIA, $teacherInfo['userId'], $assignmentId, [
                'assignmentName' => $assignmentInfo['assignmentName'] ?? 'Unknown',
                'teacherName' => $teacherInfo['userName'],
                'studentName' => $studentInfo['userName'],
                'studentId' => $studentId,
                'criteriaChanges' => $criteriaChanges,
                'changesCount' => count($criteriaChanges),
                'subjectId' => $assignmentInfo['subjectId'] ?? null
            ]);
        }
    }
}
