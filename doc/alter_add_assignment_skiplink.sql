-- Add assignmentSkipLinkCheck column to assignments table and update existing rows
-- Run this in your MariaDB (inside container or via mysql client)

-- 1) Add the column with default 0 (safe, non-destructive):
ALTER TABLE `assignments`
  ADD COLUMN `assignmentSkipLinkCheck` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `assignmentInvolvesOpenApi`;

-- 2) (Optional) Ensure any NULL values (if any) are set to 0 explicitly:
UPDATE `assignments` SET `assignmentSkipLinkCheck` = 0 WHERE `assignmentSkipLinkCheck` IS NULL;

