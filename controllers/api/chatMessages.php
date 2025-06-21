<?php

namespace App\Controllers\Api;

use App\Db;

/**
 * ChatMessages API Controller
 * Handles AJAX requests for chat messages functionality
 */
class ChatMessagesController
{
    /**
     * Get all messages for a specific assignment
     * 
     * @param int $assignmentId The ID of the assignment
     * @param int $requestUserId Optional user ID from request to override global userId (for teachers fetching student messages)
     * @return array JSON response with messages
     */
    public function getMessages($assignmentId, $requestUserId = null)
    {
        global $userId;

        // If userId was provided in the request, use that instead of the global one
        // This allows teachers to fetch messages for a specific student
        $targetUserId = $requestUserId ? $requestUserId : $userId;

        if (empty($assignmentId)) {
            return ['status' => 400, 'message' => 'Assignment ID is required'];
        }

        $messages = Db::getAll("
            SELECT 
                cm.messageId, 
                cm.assignmentId, 
                cm.senderId, 
                cm.recipientId, 
                cm.message, 
                cm.createdAt,
                sender.userName as senderName,
                recipient.userName as recipientName
            FROM 
                chatMessages cm
            JOIN 
                users sender ON cm.senderId = sender.userId
            JOIN 
                users recipient ON cm.recipientId = recipient.userId
            WHERE 
                cm.assignmentId = ?
            ORDER BY 
                cm.createdAt ASC
        ", [$assignmentId]);

        return [
            'status' => 200,
            'messages' => $messages
        ];
    }

    /**
     * Add a new message for an assignment
     * 
     * @param int $assignmentId The ID of the assignment
     * @param int $recipientId The ID of the recipient
     * @param string $message The message content
     * @return array JSON response with status
     */
    public function addMessage($assignmentId, $recipientId, $message)
    {
        global $userId;

        if (empty($assignmentId) || empty($recipientId) || empty($message)) {
            return ['error' => 'Assignment ID, recipient ID, and message are required'];
        }

        // Create the message
        $messageId = Db::insert("
            INSERT INTO chatMessages (assignmentId, senderId, recipientId, message, createdAt) 
            VALUES (?, ?, ?, ?, NOW())
        ", [$assignmentId, $userId, $recipientId, $message]);

        // Get the newly created message with user information
        $newMessage = Db::getFirst("
            SELECT 
                cm.messageId, 
                cm.assignmentId, 
                cm.senderId, 
                cm.recipientId, 
                cm.message, 
                cm.createdAt,
                sender.userName as senderName,
                recipient.userName as recipientName
            FROM 
                chatMessages cm
            JOIN 
                users sender ON cm.senderId = sender.userId
            JOIN 
                users recipient ON cm.recipientId = recipient.userId
            WHERE 
                cm.messageId = ?
        ", [$messageId]);

        return [
            'success' => true,
            'message' => $newMessage
        ];
    }

    /**
     * Get recent messages for a teacher across all assignments
     * 
     * @param int $limit Optional limit on number of messages
     * @return array JSON response with recent messages
     */
    public function getRecentMessages($limit = 10)
    {
        global $userId, $auth;

        // Check if user is a teacher
        if ($auth->userRole !== 'teacher') {
            return ['error' => 'Only teachers can view recent messages'];
        }

        $messages = Db::getAll("
            SELECT 
                cm.messageId, 
                cm.assignmentId, 
                cm.senderId, 
                cm.recipientId, 
                cm.message, 
                cm.createdAt,
                sender.userName as senderName,
                recipient.userName as recipientName
            FROM 
                chatMessages cm
            JOIN 
                users sender ON cm.senderId = sender.userId
            JOIN 
                users recipient ON cm.recipientId = recipient.userId
            WHERE 
                cm.recipientId = ?
            ORDER BY 
                cm.createdAt DESC
            LIMIT ?
        ", [$userId, $limit]);

        return [
            'success' => true,
            'messages' => $messages
        ];
    }
}
