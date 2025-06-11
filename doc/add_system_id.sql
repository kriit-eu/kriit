-- Add systemId to the database schema for multi-system support
ALTER TABLE subjects ADD COLUMN systemId INT UNSIGNED NOT NULL DEFAULT 1 AFTER subjectExternalId;
ALTER TABLE assignments ADD COLUMN systemId INT UNSIGNED NOT NULL DEFAULT 1 AFTER assignmentExternalId;
ALTER TABLE users ADD COLUMN systemId INT UNSIGNED DEFAULT 1 AFTER userExternalId;

-- Drop existing unique indexes
ALTER TABLE subjects DROP INDEX idx_subjects_subjectExternalId;
ALTER TABLE assignments DROP INDEX idx_assignments_assignmentExternalId;
ALTER TABLE users DROP INDEX idx_users_userExternalId;

-- Create new composite unique indexes combining externalId and systemId
ALTER TABLE subjects ADD UNIQUE INDEX idx_subjects_ext_system (subjectExternalId, systemId);
ALTER TABLE assignments ADD UNIQUE INDEX idx_assignments_ext_system (assignmentExternalId, systemId);
ALTER TABLE users ADD UNIQUE INDEX idx_users_ext_system (userExternalId, systemId);

-- Create systems table
CREATE TABLE `systems` (
  `systemId` int unsigned NOT NULL AUTO_INCREMENT,
  `systemName` varchar(50) NOT NULL,
  `systemUrl` varchar(191) DEFAULT NULL,
  `systemApiKey` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`systemId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default system
INSERT INTO systems (systemId, systemName) VALUES (1, 'Tahvel');
