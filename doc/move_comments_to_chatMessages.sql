-- Migration: Move old userAssignments.comments to chatMessages
-- Date: 2025-06-20

-- This script migrates all comments from userAssignments.comments (JSON) to chatMessages

DELIMITER $$

CREATE PROCEDURE migrate_userAssignments_comments_to_chatMessages()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE ua_user_id INT;
    DECLARE ua_assignment_id INT;
    DECLARE comments_json TEXT;
    DECLARE comment_idx INT;
    DECLARE comment_count INT;
    DECLARE comment_text TEXT;
    DECLARE comment_name VARCHAR(255);
    DECLARE comment_created DATETIME;
    DECLARE teacher_id INT;
    DECLARE recipient_id INT;
    DECLARE ua_graded_at DATETIME;

    -- Cursor for userAssignments with non-empty comments
    DECLARE cur CURSOR FOR
        SELECT userId, assignmentId, comments, userAssignmentGradedAt FROM userAssignments WHERE comments IS NOT NULL AND comments != '[]' AND comments != '';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO ua_user_id, ua_assignment_id, comments_json, ua_graded_at;
        IF done THEN
            LEAVE read_loop;
        END IF;
        -- Find teacher for this assignment
        SELECT s.teacherId INTO teacher_id
        FROM assignments a
        JOIN subjects s ON a.subjectId = s.subjectId
        WHERE a.assignmentId = ua_assignment_id
        LIMIT 1;
        SET comment_idx = 0;
        SET comment_count = JSON_LENGTH(comments_json);
        comment_loop: WHILE comment_idx < comment_count DO
            SET comment_text = JSON_UNQUOTE(JSON_EXTRACT(comments_json, CONCAT('$[', comment_idx, '].comment')));
            SET comment_name = JSON_UNQUOTE(JSON_EXTRACT(comments_json, CONCAT('$[', comment_idx, '].name')));
            
            -- Get the createdAt value as string first
            SET @temp_created = JSON_UNQUOTE(JSON_EXTRACT(comments_json, CONCAT('$[', comment_idx, '].createdAt')));
            
            -- Check if it's a valid datetime we can use
            IF @temp_created IS NULL OR @temp_created = 'null' OR @temp_created = '' OR @temp_created = 'NULL' OR 
               @temp_created = '0000-00-00 00:00:00' OR @temp_created = '1970-01-01 00:00:00' THEN
                -- Use graded date as fallback
                SET comment_created = ua_graded_at;
            ELSE
                -- Try to convert the string to datetime
                SET comment_created = STR_TO_DATE(@temp_created, '%Y-%m-%d %H:%i:%s');
            END IF;
            
            -- Final fallback if everything else failed
            IF comment_created IS NULL THEN
                SET comment_created = NOW();
            END IF;
            
            -- Determine sender and recipient based on comment author name
            -- ua_user_id is always the student (whose userAssignment this is)
            -- comment_name tells us who actually wrote the comment
            
            -- Get teacher name to compare with comment author
            SET @teacher_name = (SELECT u.userName 
                                FROM users u 
                                WHERE u.userId = teacher_id 
                                LIMIT 1);
            
            IF comment_name = @teacher_name THEN
                -- Teacher wrote the comment: teacher is sender, student is recipient
                INSERT INTO chatMessages (assignmentId, senderId, recipientId, message, createdAt)
                VALUES (ua_assignment_id, teacher_id, ua_user_id, comment_text, comment_created);
            ELSE
                -- Student wrote the comment: student is sender, teacher is recipient
                INSERT INTO chatMessages (assignmentId, senderId, recipientId, message, createdAt)
                VALUES (ua_assignment_id, ua_user_id, teacher_id, comment_text, comment_created);
            END IF;
            SET comment_idx = comment_idx + 1;
        END WHILE;
    END LOOP;
    CLOSE cur;
END$$

DELIMITER ;

-- To run the migration:
-- CALL migrate_userAssignments_comments_to_chatMessages();