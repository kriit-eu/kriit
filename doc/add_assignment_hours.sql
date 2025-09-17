-- Migration to add assignmentHours column to assignments table
-- Run inside the database container or with proper DB access
-- Example (from host):
-- mysql -h 127.0.0.1 -P 8002 -u root -pkriitkriit kriit < add_assignment_hours.sql

ALTER TABLE `assignments`
  ADD COLUMN `assignmentHours` SMALLINT UNSIGNED DEFAULT NULL AFTER `assignmentDueAt`;

-- After running migration, update doc/database.sql with refreshdb.php --dump per project conventions
