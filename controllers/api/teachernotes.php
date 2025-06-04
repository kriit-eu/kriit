<?php namespace App\api;

use App\Controller;
use App\Db;

/**
 * API Controller for Teacher Notes functionality
 * Handles CRUD operations for teacher's private notes on student assignments
 */
class teachernotes extends Controller
{
    /**
     * Get teacher's private notes for a specific assignment and student
     */
    public function index(): void
    {
        try {
            // Check if user is a teacher or admin
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

            // Log the request for debugging
            error_log("getTeacherNotes - assignmentId: $assignmentId, studentId: $studentId, teacherId: " . $this->auth->userId);

            // Get teacher notes for this assignment and student
            $notes = Db::getFirst("
                SELECT noteContent, createdAt, updatedAt 
                FROM teacherNotes 
                WHERE assignmentId = ? AND studentId = ? AND teacherId = ?
            ", [$assignmentId, $studentId, $this->auth->userId]);

            stop(200, [
                'notes' => $notes['noteContent'] ?? '',
                'createdAt' => $notes['createdAt'] ?? null,
                'updatedAt' => $notes['updatedAt'] ?? null
            ]);

        } catch (\Exception $e) {
            error_log("Error in getTeacherNotes: " . $e->getMessage());
            stop(500, 'Internal server error: ' . $e->getMessage());
        }
    }

    /**
     * Save teacher's private notes for a specific assignment and student
     */
    public function save(): void
    {
        try {
            // Check if user is a teacher or admin
            if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
                stop(403, 'Access denied');
            }

            $assignmentId = $_POST['assignmentId'] ?? null;
            $studentId = $_POST['studentId'] ?? null;
            $noteContent = $_POST['noteContent'] ?? '';

            if (!$assignmentId) {
                stop(400, 'Assignment ID required');
            }

            if (!$studentId) {
                stop(400, 'Student ID required');
            }

            // Log the input for debugging
            error_log("saveTeacherNotes - assignmentId: $assignmentId, studentId: $studentId, teacherId: " . $this->auth->userId);

            // Check if notes already exist for this teacher, assignment, and student
            $existingNote = Db::getFirst("
                SELECT noteId 
                FROM teacherNotes 
                WHERE assignmentId = ? AND studentId = ? AND teacherId = ?
            ", [$assignmentId, $studentId, $this->auth->userId]);

            if ($existingNote) {
                // Update existing note
                if (trim($noteContent) === '') {
                    // If content is empty, delete the note
                    Db::delete('teacherNotes', 'noteId = ?', [$existingNote['noteId']]);
                } else {
                    // Update existing note
                    Db::update('teacherNotes', [
                        'noteContent' => $noteContent,
                        'updatedAt' => date('Y-m-d H:i:s')
                    ], 'noteId = ?', [$existingNote['noteId']]);
                }
            } else {
                // Create new note only if content is not empty
                if (trim($noteContent) !== '') {
                    Db::insert('teacherNotes', [
                        'assignmentId' => $assignmentId,
                        'studentId' => $studentId,
                        'teacherId' => $this->auth->userId,
                        'noteContent' => $noteContent,
                        'createdAt' => date('Y-m-d H:i:s'),
                        'updatedAt' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            stop(200, ['success' => true]);

        } catch (\Exception $e) {
            error_log("Error in saveTeacherNotes: " . $e->getMessage());
            stop(500, 'Internal server error: ' . $e->getMessage());
        }
    }
}
