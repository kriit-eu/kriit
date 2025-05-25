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

        // Fetch all submissions ordered by submission time descending
        $submissions = Db::getAll("
            SELECT
                ua.userAssignmentSubmittedAt AS 'Aeg',
                u.userName AS 'Õpilane',
                s.subjectName AS 'Aine',
                a.assignmentName AS 'Ülesanne',
                s.subjectId
            FROM userAssignments ua
            JOIN users u ON ua.userId = u.userId
            JOIN assignments a ON ua.assignmentId = a.assignmentId
            JOIN subjects s ON a.subjectId = s.subjectId
            WHERE ua.userAssignmentSubmittedAt IS NOT NULL
            ORDER BY ua.userAssignmentSubmittedAt DESC
        ");

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
}
