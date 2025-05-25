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
    function index(): void
    {
        // Check if user is a teacher
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            $this->redirect('');
            exit();
        }

        // Fetch all submissions with comment counts
        $submissions = Db::getAll("
            SELECT
                ua.userAssignmentSubmittedAt AS 'Aeg',
                u.userName AS 'Õpilane',
                s.subjectName AS 'Aine',
                a.assignmentName AS 'Ülesanne',
                s.subjectId,
                ua.userId,
                ua.assignmentId,
                ua.solutionUrl,
                ua.comments,
                a.assignmentInstructions,
                (
                    SELECT COUNT(m.messageId)
                    FROM messages m
                    LEFT JOIN users mu ON m.userId = mu.userId
                    WHERE m.assignmentId = ua.assignmentId
                    AND m.isNotification = 0
                    AND (m.userId = ua.userId OR mu.userIsTeacher = 1 OR mu.userIsAdmin = 1)
                ) AS commentCount
            FROM userAssignments ua
            JOIN users u ON ua.userId = u.userId
            JOIN assignments a ON ua.assignmentId = a.assignmentId
            JOIN subjects s ON a.subjectId = s.subjectId
            WHERE ua.userAssignmentSubmittedAt IS NOT NULL
            ORDER BY ua.userAssignmentSubmittedAt ASC
        ");

        // Calculate days ago for each submission
        foreach ($submissions as &$submission) {
            if ($submission['Aeg']) {
                $submissionDate = new \DateTime($submission['Aeg']);
                $today = new \DateTime();
                $today->setTime(0, 0, 0); // Set to start of day for accurate comparison
                $submissionDate->setTime(0, 0, 0); // Set to start of day for accurate comparison

                $daysAgo = $today->diff($submissionDate)->days;
                $submission['Vanus'] = $daysAgo == 0 ? '' : $daysAgo;
            } else {
                $submission['Vanus'] = '';
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
    function getMessages(): void
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
    function saveMessage(): void
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

        // Save the message (teacher's message in the context of this assignment)
        Db::insert('messages', [
            'assignmentId' => $assignmentId,
            'userId' => $this->auth->userId,
            'content' => trim($content),
            'CreatedAt' => date('Y-m-d H:i:s'),
            'isNotification' => 0
        ]);

        stop(200, 'Message saved');
    }
}
