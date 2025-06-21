-- Create chatMessages table for Vestlus section
-- Date: 2025-06-20

CREATE TABLE `chatMessages` (
    `messageId` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `assignmentId` INT UNSIGNED DEFAULT NULL,
    `senderId` INT UNSIGNED NOT NULL,
    `recipientId` INT UNSIGNED NOT NULL,
    `message` TEXT NOT NULL,
    `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`messageId`),
    KEY `idx_assignmentId` (`assignmentId`),
    KEY `idx_senderId` (`senderId`),
    KEY `idx_recipientId` (`recipientId`),
    CONSTRAINT `fk_chat_assignment` FOREIGN KEY (`assignmentId`) REFERENCES `assignments`(`assignmentId`) ON DELETE CASCADE,
    CONSTRAINT `fk_chat_sender` FOREIGN KEY (`senderId`) REFERENCES `users`(`userId`) ON DELETE CASCADE,
    CONSTRAINT `fk_chat_recipient` FOREIGN KEY (`recipientId`) REFERENCES `users`(`userId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
