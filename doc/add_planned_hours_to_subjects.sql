-- Add subjectPlannedHours column to subjects table
ALTER TABLE subjects ADD COLUMN subjectPlannedHours SMALLINT UNSIGNED NULL DEFAULT NULL COMMENT 'Planned independent work hours for the subject';
