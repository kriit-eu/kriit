-- Migration: Recreate view userExercisesWithComputedStatus without DEFINER
-- Date: 2025-09-24

START TRANSACTION;

-- Drop the existing view if present
DROP VIEW IF EXISTS `userExercisesWithComputedStatus`;

-- Recreate the view with SQL SECURITY INVOKER so it doesn't depend on a DEFINER user
CREATE SQL SECURITY INVOKER VIEW `userExercisesWithComputedStatus` AS
SELECT 
    ue.userId,
    ue.exerciseId,
    ue.startTime,
    ue.endTime,
    u.userTimeUpAt,
    CASE 
        WHEN ue.startTime IS NULL THEN 'not_started'
        WHEN ue.endTime IS NOT NULL THEN 'completed'
        WHEN ue.startTime IS NOT NULL AND ue.endTime IS NULL AND u.userTimeUpAt IS NOT NULL AND DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR) > u.userTimeUpAt THEN 'timed_out'
        WHEN ue.startTime IS NOT NULL AND ue.endTime IS NULL THEN 'started'
        ELSE 'not_started'
    END as status,
    CASE 
        WHEN ue.startTime IS NULL THEN NULL
        WHEN ue.endTime IS NOT NULL THEN TIMESTAMPDIFF(SECOND, ue.startTime, ue.endTime)
        WHEN u.userTimeUpAt IS NULL THEN TIMESTAMPDIFF(SECOND, ue.startTime, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR))
        WHEN DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR) > u.userTimeUpAt THEN TIMESTAMPDIFF(SECOND, ue.startTime, u.userTimeUpAt)
        ELSE TIMESTAMPDIFF(SECOND, ue.startTime, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR))
    END as durationSeconds
FROM userExercises ue 
JOIN users u ON u.userId = ue.userId;

COMMIT;
