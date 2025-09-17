-- Add plannedHours column to subjects table
ALTER TABLE subjects ADD COLUMN plannedHours SMALLINT UNSIGNED NULL DEFAULT NULL COMMENT 'Planned independent work hours for the subject';
