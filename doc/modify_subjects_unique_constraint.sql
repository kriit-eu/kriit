-- Modify subjects table unique constraint to include groupId
-- This script updates the unique constraint on the subjects table

-- Drop the old unique constraint
ALTER TABLE subjects DROP INDEX idx_subjects_ext_system;

-- Add new unique constraint that includes groupId
ALTER TABLE subjects ADD UNIQUE KEY idx_subjects_ext_system_group (subjectExternalId, systemId, groupId);
