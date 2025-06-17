-- Add comments table to replace the conversation functionality
-- This table will store comments for assignments with better structure

CREATE TABLE IF NOT EXISTS `comments` (
  `commentId` int unsigned NOT NULL AUTO_INCREMENT,
  `assignmentId` int unsigned NOT NULL COMMENT 'Assignment ID',
  `senderId` int unsigned NOT NULL COMMENT 'User who sent the comment',
  `content` text NOT NULL COMMENT 'Comment content',
  `parentCommentId` int unsigned DEFAULT NULL COMMENT 'Parent comment ID for replies',
  `targetStudentId` int unsigned DEFAULT NULL COMMENT 'Specific student ID if this comment is directed to a specific student',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`commentId`),
  KEY `idx_comments_assignment` (`assignmentId`),
  KEY `idx_comments_sender` (`senderId`),
  KEY `idx_comments_parent` (`parentCommentId`),
  KEY `idx_comments_created` (`createdAt`),
  KEY `idx_comments_target_student` (`targetStudentId`),
  CONSTRAINT `fk_comments_assignment` FOREIGN KEY (`assignmentId`) REFERENCES `assignments` (`assignmentId`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_sender` FOREIGN KEY (`senderId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_target_student` FOREIGN KEY (`targetStudentId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parentCommentId`) REFERENCES `comments` (`commentId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comments for assignments with support for threaded replies';

-- Add indexes for better performance
CREATE INDEX `idx_comments_assignment_created` ON `comments` (`assignmentId`, `createdAt`); 