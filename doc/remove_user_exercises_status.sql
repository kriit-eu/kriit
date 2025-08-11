-- Migration: Remove 'status' column from 'userExercises' table
ALTER TABLE `userExercises` DROP COLUMN `status`;
