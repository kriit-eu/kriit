-- Add assignmentLessons column to assignments table
ALTER TABLE `assignments`
ADD COLUMN `assignmentLessons` SMALLINT UNSIGNED DEFAULT NULL AFTER `assignmentHours`;
