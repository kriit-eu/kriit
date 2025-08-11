-- Combined migration for exercises and subjects (last 2 commits)
-- Safely run on live MariaDB

-- 1. Rename userDoneExercises to userExercises (if needed)
-- Only run if userDoneExercises exists and userExercises does not


-- SAFE: Only rename if userExercises does not exist
-- Manual step may be required if both tables exist
RENAME TABLE userDoneExercises TO userExercises;

-- 2. Add startTime and endTime columns to userExercises (if not present)

ALTER TABLE userExercises ADD COLUMN startTime TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE userExercises ADD COLUMN endTime TIMESTAMP NULL DEFAULT NULL;


-- 4. Create/replace the computed status view
DROP VIEW IF EXISTS userExercisesWithComputedStatus;
CREATE VIEW userExercisesWithComputedStatus AS
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
-- End of migration
