-- 2025-09-23: Add `criterionOrderNr` column and backfill per-assignment ordering
-- This combined migration will:
--  1) Add the `criterionOrderNr` column if it doesn't exist
--  2) Attempt a safe backfill using window functions (ROW_NUMBER())
-- If the server does not support window functions the script aborts before updating and
-- you should run the PHP backfill or the legacy SQL fallback.

-- Usage (run inside container or where mysql client can reach DB):
-- mysql -h db -u root -pkriitkriit kriit < doc/migrations/2025-09-23__add_criterionOrderNr__alter_and_backfill.sql

-- 1) Add column if missing
ALTER TABLE `criteria` ADD COLUMN IF NOT EXISTS `criterionOrderNr` INT DEFAULT NULL;

-- 2) Test for ROW_NUMBER() support and run transactional backfill
-- Prepare/execute a harmless ROW_NUMBER() statement. PREPARE will fail on unsupported servers and stop the script.
SET @test_sql = 'SELECT ROW_NUMBER() OVER (PARTITION BY assignmentId ORDER BY criterionId) AS rn FROM criteria LIMIT 1';
PREPARE chk_stmt FROM @test_sql;
EXECUTE chk_stmt;
DEALLOCATE PREPARE chk_stmt;

-- If we reach here, ROW_NUMBER is supported — update inside a transaction
START TRANSACTION;

UPDATE criteria c
JOIN (
  SELECT criterionId,
         ROW_NUMBER() OVER (
           PARTITION BY assignmentId
           ORDER BY (criterionOrderNr IS NULL), criterionOrderNr ASC, criterionId ASC
         ) AS rn
  FROM criteria
) t ON c.criterionId = t.criterionId
SET c.criterionOrderNr = t.rn;

COMMIT;

-- End of migration. If PREPARE failed above, no update ran and you can run the PHP fallback:
-- php scripts/add_criterion_order_column.php
