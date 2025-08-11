-- Create a view that computes exercise status automatically
-- This replaces direct userExercises table access with computed status logic

-- Drop existing view if exists
DROP VIEW IF EXISTS userExercisesWithComputedStatus;

-- Create the view with computed status
CREATE VIEW userExercisesWithComputedStatus AS
SELECT 
    ue.userId,
    ue.exerciseId,
    ue.startTime,
    ue.endTime,
    u.userTimeUpAt,
    -- Computed status based on database values
    CASE 
        WHEN ue.startTime IS NULL THEN 'not_started'
        WHEN ue.endTime IS NOT NULL THEN 'completed'
        WHEN ue.startTime IS NOT NULL AND ue.endTime IS NULL AND u.userTimeUpAt IS NOT NULL AND DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR) > u.userTimeUpAt THEN 'timed_out'
        WHEN ue.startTime IS NOT NULL AND ue.endTime IS NULL THEN 'started'
        ELSE 'not_started'
    END as status,
    -- Computed duration for convenience
    CASE 
        WHEN ue.startTime IS NULL THEN NULL
        WHEN ue.endTime IS NOT NULL THEN TIMESTAMPDIFF(SECOND, ue.startTime, ue.endTime)
        WHEN u.userTimeUpAt IS NULL THEN TIMESTAMPDIFF(SECOND, ue.startTime, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR)) -- Admin, no limit
        WHEN DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR) > u.userTimeUpAt THEN TIMESTAMPDIFF(SECOND, ue.startTime, u.userTimeUpAt) -- Cap at time limit
        ELSE TIMESTAMPDIFF(SECOND, ue.startTime, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR)) -- Currently running
    END as durationSeconds
FROM userExercises ue 
JOIN users u ON u.userId = ue.userId;

-- Test the view
SELECT 
    userId,
    exerciseId,
    startTime,
    endTime,
    userTimeUpAt,
    status,
    durationSeconds,
    -- Format duration as HH:MM:SS
    CASE 
        WHEN durationSeconds IS NULL THEN '-'
        ELSE CONCAT(
            LPAD(FLOOR(durationSeconds / 3600), 2, '0'), ':',
            LPAD(FLOOR((durationSeconds % 3600) / 60), 2, '0'), ':',
            LPAD(durationSeconds % 60, 2, '0')
        )
    END as formattedDuration
FROM userExercisesWithComputedStatus
ORDER BY userId, exerciseId
LIMIT 10;