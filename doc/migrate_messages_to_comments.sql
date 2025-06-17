-- Migration script to import existing messages to the new comments table
-- This script will transfer all conversation data from the messages table to the comments table

-- First, ensure the comments table exists
-- (Run the add_comments_table.sql first if not already done)

-- Import all non-notification messages (actual conversation messages)
INSERT INTO comments (
    assignmentId,
    senderId,
    content,
    parentCommentId,
    targetStudentId,
    createdAt,
    updatedAt
)
SELECT 
    m.assignmentId,
    m.userId as senderId,
    m.content,
    NULL as parentCommentId, -- No threading in old messages
    NULL as targetStudentId, -- No specific targeting in old messages
    m.CreatedAt as createdAt,
    m.CreatedAt as updatedAt
FROM messages m
WHERE m.isNotification = 0
ORDER BY m.CreatedAt ASC;

-- Import notification messages as system comments
-- These will be marked with a special content prefix to identify them as system messages
INSERT INTO comments (
    assignmentId,
    senderId,
    content,
    parentCommentId,
    targetStudentId,
    createdAt,
    updatedAt
)
SELECT 
    m.assignmentId,
    m.userId as senderId,
    CONCAT('[SYSTEM] ', m.content) as content,
    NULL as parentCommentId,
    NULL as targetStudentId,
    m.CreatedAt as createdAt,
    m.CreatedAt as updatedAt
FROM messages m
WHERE m.isNotification = 1
ORDER BY m.CreatedAt ASC;

-- Display migration summary
SELECT 
    'Migration Summary' as info,
    COUNT(*) as total_messages_migrated,
    SUM(CASE WHEN content LIKE '[SYSTEM]%' THEN 1 ELSE 0 END) as system_messages,
    SUM(CASE WHEN content NOT LIKE '[SYSTEM]%' THEN 1 ELSE 0 END) as conversation_messages
FROM comments;

-- Optional: Create a backup of the original messages table
-- CREATE TABLE messages_backup AS SELECT * FROM messages; 