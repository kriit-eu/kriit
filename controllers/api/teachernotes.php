<?php namespace App\api;

use App\Controller;
use App\Db;

/**
 * API Controller for Teacher Notes functionality
 * Handles CRUD operations for shared teacher notes on student assignments
 * @noinspection PhpUnused
 * Auto-loaded by MVC framework for /api/outcomes/* routes
 */
class teachernotes extends Controller
{
    /**
     * Get shared teacher notes for a specific assignment and student
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
            error_log("getTeacherNotes - assignmentId: $assignmentId, studentId: $studentId, requesterId: " . $this->auth->userId);

            // Get the most recent shared teacher note for this assignment and student
            $notes = Db::getFirst("
                SELECT tn.noteContent, tn.createdAt, tn.updatedAt, u.userName as updatedBy
                FROM teacherNotes tn
                LEFT JOIN users u ON u.userId = tn.teacherId
                WHERE tn.assignmentId = ? AND tn.studentId = ?
                ORDER BY tn.updatedAt DESC
                LIMIT 1
            ", [$assignmentId, $studentId]);

            stop(200, [
                'notes' => $notes['noteContent'] ?? '',
                'createdAt' => $notes['createdAt'] ?? null,
                'updatedAt' => $notes['updatedAt'] ?? null,
                'updatedBy' => $notes['updatedBy'] ?? null
            ]);

        } catch (\Exception $e) {
            error_log("Error in getTeacherNotes: " . $e->getMessage());
            stop(500, 'Internal server error: ' . $e->getMessage());
        }
    }

    /**
     * Save shared teacher notes for a specific assignment and student
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

            // Check if any notes already exist for this assignment and student
            $existingNote = Db::getFirst("
                SELECT noteId 
                FROM teacherNotes 
                WHERE assignmentId = ? AND studentId = ?
                ORDER BY updatedAt DESC
                LIMIT 1
            ", [$assignmentId, $studentId]);

            if ($existingNote) {
                // Update existing note with new teacher as the updater
                if (trim($noteContent) === '') {
                    // If content is empty, delete all notes for this assignment and student
                    Db::delete('teacherNotes', 'assignmentId = ? AND studentId = ?', [$assignmentId, $studentId]);
                } else {
                    // Update existing note
                    Db::update('teacherNotes', [
                        'noteContent' => $noteContent,
                        'teacherId' => $this->auth->userId, // Track who last updated
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
