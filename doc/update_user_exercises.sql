-- Rename userDoneExercises to userExercises
ALTER TABLE `userDoneExercises` RENAME TO `userExercises`;

-- Add new columns to userExercises
ALTER TABLE `userExercises`
  ADD COLUMN `startTime` TIMESTAMP NULL,
  ADD COLUMN `endTime` TIMESTAMP NULL,
  ADD COLUMN `status` ENUM('not_started', 'started', 'completed', 'timed_out') NOT NULL DEFAULT 'not_started';
