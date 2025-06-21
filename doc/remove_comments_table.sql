-- Remove comments field from userAssignments table
-- Date: 2025-06-20

ALTER TABLE `userAssignments`
  DROP COLUMN `comments`;
