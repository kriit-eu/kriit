-- Migration: Add 'status' column to 'userExercises' and backfill values
-- This column mirrors the computed status in the view `userExercisesWithComputedStatus`.

SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS=0;

-- We'll create a temporary enum column, backfill it using the same logic
-- then drop the old column (if present) and rename the temp column to `status`.

-- Create temporary ENUM column (nullable to allow backfill without strict checks)
ALTER TABLE `userExercises`
  ADD COLUMN `status_tmp` ENUM('not_started','started','completed','timed_out') DEFAULT NULL;

-- Backfill values into the temporary enum column based on existing data and users.userTimeUpAt
UPDATE userExercises ue
JOIN users u ON u.userId = ue.userId
SET ue.status_tmp = (
  CASE
    WHEN ue.startTime IS NULL THEN 'not_started'
    WHEN ue.endTime IS NOT NULL THEN 'completed'
    WHEN ue.startTime IS NOT NULL AND ue.endTime IS NULL AND u.userTimeUpAt IS NOT NULL AND UTC_TIMESTAMP() + INTERVAL 3 HOUR > u.userTimeUpAt THEN 'timed_out'
    WHEN ue.startTime IS NOT NULL AND ue.endTime IS NULL THEN 'started'
    ELSE 'not_started'
  END
);

-- For any rows not covered above (shouldn't happen), set default in temp column
UPDATE userExercises SET status_tmp = 'not_started' WHERE status_tmp IS NULL;

-- If an existing `status` column exists (e.g. older schema), drop it now
-- (this is a no-op if it does not exist; some MySQL versions require conditional handling outside SQL)
ALTER TABLE `userExercises` DROP COLUMN IF EXISTS `status`;

-- Rename temp column to final name and set NOT NULL with default
ALTER TABLE `userExercises`
  CHANGE COLUMN `status_tmp` `status` ENUM('not_started','started','completed','timed_out') NOT NULL DEFAULT 'not_started';

-- Add an index to help queries filtering by status (used by many views/queries)
ALTER TABLE `userExercises` ADD KEY `idx_userExercises_status` (`status`);

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;

-- END Migration
