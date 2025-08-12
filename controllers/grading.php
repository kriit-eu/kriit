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

        // Check if we should show graded assignments
        $this->showGraded = isset($_GET['showGraded']) && $_GET['showGraded'] == '1';

        // Determine which assignment statuses to include
        $statusFilter = $this->showGraded ? 'assignmentStatusId IN (2, 3)' : 'assignmentStatusId = 2';

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
                a.assignmentInvolvesOpenApi,
                (
                    SELECT COUNT(m.messageId)
                    FROM messages m
                    LEFT JOIN users mu ON m.userId = mu.userId
                    WHERE m.assignmentId = ua.assignmentId
                    AND m.isNotification = 0
                    AND (m.userId = ua.userId OR mu.userIsTeacher = 1 OR mu.userIsAdmin = 1)
                ) AS commentCount,
                (
                    SELECT COUNT(tn.noteId)
                    FROM teacherNotes tn
                    WHERE tn.assignmentId = ua.assignmentId
                    AND tn.studentId = ua.userId
                    AND tn.noteContent IS NOT NULL
                    AND tn.noteContent != ''
                ) AS teacherNotesCount,
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
            WHERE $statusFilter
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

        // Unset the reference to prevent bugs in subsequent loops
        unset($submission);

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

        // Fetch comments from userAssignments for this specific student
        $userAssignment = Db::getFirst("
            SELECT comments 
            FROM userAssignments 
            WHERE userId = ? AND assignmentId = ?
        ", [$studentId, $assignmentId]);

        // Get comments from the JSON field
        $comments = $userAssignment && $userAssignment['comments'] ? json_decode($userAssignment['comments'], true) : [];
        
        // Also fetch system notifications from messages table (like grade changes)
        $systemMessages = Db::getAll("
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
            AND m.isNotification = 1
            ORDER BY m.CreatedAt ASC
        ", [$assignmentId]);

        // Format messages - combine comments and system notifications
        $formattedMessages = [];
        
        // Add comments from userAssignments
        foreach ($comments as $comment) {
            // Look up userId based on userName
            $userId = null;
            if (!empty($comment['name'])) {
                $user = Db::getFirst("SELECT userId FROM users WHERE userName = ?", [$comment['name']]);
                $userId = $user ? $user['userId'] : null;
            }
            
            $formattedMessages[] = [
                'messageId' => null, // Comments don't have messageId
                'content' => $comment['comment'],
                'userId' => $userId,
                'userName' => $comment['name'],
                'createdAt' => $comment['createdAt'],
                'isNotification' => false
            ];
        }
        
        // Add system notifications
        foreach ($systemMessages as $message) {
            $formattedMessages[] = [
                'messageId' => $message['messageId'],
                'content' => $message['content'],
                'userId' => $message['userId'],
                'userName' => $message['userName'],
                'createdAt' => $message['CreatedAt'],
                'isNotification' => $message['isNotification']
            ];
        }
        
        // Sort by creation time
        usort($formattedMessages, function($a, $b) {
            return strtotime($a['createdAt']) - strtotime($b['createdAt']);
        });

        stop(200, $formattedMessages);
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
        $imageId = $_POST['imageId'] ?? null;
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
        $isUpdated = $this->saveOrUpdateUserAssignment($studentId, $assignmentId, $grade, $comment, $imageId, $teacherInfo, $studentInfo);

        // Send notification email to student
        $this->sendGradeNotification($assignmentId, $studentId, $teacherInfo, $isUpdated);

        // Get the updated grading information to return to the client
        $userAssignment = Db::getFirst('SELECT userAssignmentGradedAt FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
        $gradedAt = $userAssignment['userAssignmentGradedAt'];

        // Get submission date to calculate difference
        $submissionDate = Db::getOne('SELECT userAssignmentSubmittedAt FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);

        // Calculate difference in days
        $daysDifference = null;
        if ($submissionDate && $gradedAt) {
            $submissionDateTime = new \DateTime($submissionDate);
            $gradedDateTime = new \DateTime($gradedAt);
            $submissionDateTime->setTime(0, 0, 0);
            $gradedDateTime->setTime(0, 0, 0);
            $daysDiff = $gradedDateTime->diff($submissionDateTime)->days;
            $daysDifference = $daysDiff == 0 ? '' : $daysDiff;
        }

        stop(200, [
            'message' => 'Grade saved',
            'gradedAt' => $gradedAt,
            'daysDifference' => $daysDifference
        ]);
    }

    /**
     * Save or update user assignment with grade
     */
    private function saveOrUpdateUserAssignment($studentId, $assignmentId, $grade, $comment, $imageId, $teacherInfo, $studentInfo): bool
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
            // Use the proper comment system that targets specific students
            $this->addAssignmentCommentForStudent($studentId, $assignmentId, trim($comment), $teacherInfo['userName'], $imageId);

            // Also save a notification message for the events section
            $messageContent = "$teacherInfo[userName] lisas kommentaari õpilasele $studentInfo[userName]: " . trim($comment);
            if ($imageId) {
                $messageContent .= " (koos pildiga)";
            }
            $this->saveMessageInternal($assignmentId, $teacherInfo['userId'], $messageContent, true, $imageId);

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
    private function saveMessageInternal($assignmentId, $userId, $content, $isNotification = false, $imageId = null): void
    {
        $data = [
            'assignmentId' => $assignmentId,
            'userId' => $userId,
            'content' => $content,
            'CreatedAt' => date('Y-m-d H:i:s'),
            'isNotification' => $isNotification
        ];
        
        if ($imageId) {
            $data['imageId'] = $imageId;
        }
        
        Db::insert('messages', $data);
    }

    /**
     * Add assignment comment for specific student
     */
    private function addAssignmentCommentForStudent($studentId, $assignmentId, $comment, $commentAuthorName, $imageId = null): void
    {
        $existingComments = Db::getOne('SELECT comments FROM userAssignments WHERE userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
        $comments = $existingComments ? json_decode($existingComments, true) : [];
        $currentTime = date('Y-m-d H:i:s');

        $commentData = [
            'name' => $commentAuthorName,
            'comment' => trim($comment),
            'createdAt' => $currentTime
        ];
        
        if ($imageId) {
            $commentData['imageId'] = $imageId;
        }

        $comments[] = $commentData;

        Db::update('userAssignments', [
            'comments' => json_encode($comments)
        ], 'userId = ? AND assignmentId = ?', [$studentId, $assignmentId]);
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
