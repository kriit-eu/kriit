-- Add new activity types for syncing
INSERT INTO activities (activityId, activityName, activityDescription) VALUES
(18, 'syncStart', 'started synchronization with external system'),
(19, 'createSubjectSync', 'created subject during synchronization'),
(20, 'createAssignmentSync', 'created assignment during synchronization'),
(21, 'createUserSync', 'created user during synchronization'),
(22, 'gradeSync', 'synchronized grade'),
(23, 'updateUserName', 'updated user name during synchronization');

-- Update AUTO_INCREMENT value
ALTER TABLE activities AUTO_INCREMENT=24;
