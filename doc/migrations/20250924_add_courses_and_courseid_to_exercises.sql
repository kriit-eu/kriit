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
  `createdBy` INT UNSIGNED NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `courses_createdBy_fk` (`createdBy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure Sisseastumine course exists with id=1. If a row with id=1 exists, do not overwrite.
INSERT INTO `courses` (`id`, `name`, `description`, `visibility`, `status`, `createdAt`)
SELECT 1, 'Sisseastumine', 'Default entrance exam course', 'private', 'active', NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `courses` WHERE `id` = 1);

-- Ensure activity for creating courses exists (activityId = 30). If missing, insert it.
INSERT INTO `activities` (`activityId`, `activityName`, `activityDescription`)
SELECT 30, 'createCourse', 'created course'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `activities` WHERE `activityId` = 30);

-- Add courseId to exercises if not exists
-- Add courseId to exercises if not exists (portable)
SET @col_cnt_ex := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exercises' AND COLUMN_NAME = 'courseId');
SET @col_sql_ex := IF(@col_cnt_ex = 0,
  'ALTER TABLE `exercises` ADD COLUMN `courseId` INT NULL DEFAULT NULL',
  'SELECT 1');
PREPARE col_stmt_ex FROM @col_sql_ex;
EXECUTE col_stmt_ex;
DEALLOCATE PREPARE col_stmt_ex;

-- Migrate existing exercises to Sisseastumine (course id 1) where courseId is NULL
UPDATE `exercises` SET `courseId` = 1 WHERE `courseId` IS NULL;

-- Now make courseId NOT NULL and add FK to courses
ALTER TABLE `exercises`
  MODIFY COLUMN `courseId` INT NOT NULL;

-- Add FK fk_exercises_course if it doesn't exist
SET @fk_cnt_ex := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'exercises' AND CONSTRAINT_NAME = 'fk_exercises_course');
SET @fk_sql_ex := IF(@fk_cnt_ex = 0,
  'ALTER TABLE `exercises` ADD CONSTRAINT `fk_exercises_course` FOREIGN KEY (`courseId`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE',
  'SELECT 1');
PREPARE fk_stmt_ex FROM @fk_sql_ex;
EXECUTE fk_stmt_ex;
DEALLOCATE PREPARE fk_stmt_ex;

-- Add foreign key for courses.createdBy -> users.userId if users table exists
-- Add FK fk_courses_createdBy_users if it doesn't exist
SET @fk_cnt_courses := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'courses' AND CONSTRAINT_NAME = 'fk_courses_createdBy_users');
SET @fk_sql_courses := IF(@fk_cnt_courses = 0,
  'ALTER TABLE `courses` ADD CONSTRAINT `fk_courses_createdBy_users` FOREIGN KEY (`createdBy`) REFERENCES `users` (`userId`) ON DELETE SET NULL ON UPDATE CASCADE',
  'SELECT 1');
PREPARE fk_stmt_courses FROM @fk_sql_courses;
EXECUTE fk_stmt_courses;
DEALLOCATE PREPARE fk_stmt_courses;

-- Add courseId to assignments if not exists (keep NULLable)
-- Add courseId to assignments if not exists (portable)
SET @col_cnt_asg := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'assignments' AND COLUMN_NAME = 'courseId');
SET @col_sql_asg := IF(@col_cnt_asg = 0,
  'ALTER TABLE `assignments` ADD COLUMN `courseId` INT NULL DEFAULT NULL',
  'SELECT 1');
PREPARE col_stmt_asg FROM @col_sql_asg;
EXECUTE col_stmt_asg;
DEALLOCATE PREPARE col_stmt_asg;

-- Do not migrate existing assignment rows or force NOT NULL; leave courseId nullable.

-- Add FK to courses for assignments.courseId (allows NULLs)
-- Some MySQL/MariaDB versions don't support ADD CONSTRAINT IF NOT EXISTS.
-- Create the FK only if it doesn't already exist.
SET @fk_cnt := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'assignments' AND CONSTRAINT_NAME = 'fk_assignments_course');
SET @fk_sql := IF(@fk_cnt = 0,
  'ALTER TABLE `assignments` ADD CONSTRAINT `fk_assignments_course` FOREIGN KEY (`courseId`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE',
  'SELECT 1');
PREPARE fk_stmt FROM @fk_sql;
EXECUTE fk_stmt;
DEALLOCATE PREPARE fk_stmt;

COMMIT;

-- End migration
