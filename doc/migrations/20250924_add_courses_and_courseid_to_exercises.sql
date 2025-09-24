-- Migration: Add courses table and courseId to exercises
-- Date: 2025-09-24
-- Creates `courses` table, inserts default 'Sisseastumine' course (id=1),
-- adds `courseId` column to `exercises`, migrates existing exercises to course 1,
-- and adds foreign key constraint.

START TRANSACTION;

-- Create courses table
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `visibility` ENUM('public','private') NOT NULL DEFAULT 'private',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `sortOrder` INT NOT NULL DEFAULT 0,
  `createdBy` INT NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `courses_createdBy_fk` (`createdBy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure Sisseastumine course exists with id=1. If a row with id=1 exists, do not overwrite.
INSERT INTO `courses` (`id`, `name`, `description`, `visibility`, `status`, `sortOrder`, `createdAt`)
SELECT 1, 'Sisseastumine', 'Default entrance exam course', 'private', 'active', 0, NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `courses` WHERE `id` = 1);

-- Add courseId to exercises if not exists
ALTER TABLE `exercises`
  ADD COLUMN IF NOT EXISTS `courseId` INT NULL DEFAULT NULL;

-- Migrate existing exercises to Sisseastumine (course id 1) where courseId is NULL
UPDATE `exercises` SET `courseId` = 1 WHERE `courseId` IS NULL;

-- Now make courseId NOT NULL and add FK to courses
ALTER TABLE `exercises`
  MODIFY COLUMN `courseId` INT NOT NULL,
  ADD CONSTRAINT `fk_exercises_course` FOREIGN KEY (`courseId`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- Add foreign key for courses.createdBy -> users.userId if users table exists
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_courses_createdBy_users` FOREIGN KEY (`createdBy`) REFERENCES `users` (`userId`) ON DELETE SET NULL ON UPDATE CASCADE;

COMMIT;

-- End migration
