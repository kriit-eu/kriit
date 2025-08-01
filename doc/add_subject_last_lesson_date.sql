-- Add lastLessonDate column to subjects table
-- This stores the date of the last lesson for each subject
ALTER TABLE subjects ADD COLUMN lastLessonDate DATE NULL COMMENT 'Date of the last lesson for this subject';
