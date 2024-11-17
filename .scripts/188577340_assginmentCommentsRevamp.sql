-- Add the new column assignmentCommentId if it does not exist
ALTER TABLE assignmentComments
    ADD COLUMN IF NOT EXISTS assignmentCommentId INT UNSIGNED;

ALTER TABLE assignmentComments
    ADD COLUMN IF NOT EXISTS assignmentCommentCreatedAt DATETIME;

-- Prepopulate assignmentCommentId with increasing integers if it is not already populated
SET @count = (SELECT COALESCE(MAX(assignmentCommentId), 0) FROM assignmentComments);
select @count;
-- Check if there are rows with assignmentCommentId set to NULL
IF EXISTS (SELECT 1 FROM assignmentComments WHERE assignmentCommentId IS NULL) THEN
    -- Prepopulate assignmentCommentId with increasing integers if it is not already populated
    UPDATE assignmentComments SET assignmentCommentId = (@count := @count + 1) WHERE assignmentCommentId IS NULL;
END IF;

-- Drop the procedure if it exists
DROP PROCEDURE IF EXISTS DropForeignKeys;

-- Create a stored procedure to drop foreign key constraints
DELIMITER //

CREATE PROCEDURE DropForeignKeys()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE fk_name VARCHAR(255);
    DECLARE fk_cursor CURSOR FOR
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_NAME = 'assignmentComments'
          AND CONSTRAINT_SCHEMA = DATABASE()
          AND REFERENCED_TABLE_NAME IS NOT NULL;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN fk_cursor;

    read_loop: LOOP
        FETCH fk_cursor INTO fk_name;
        IF done THEN
            LEAVE read_loop;
        END IF;
        SET @sql = CONCAT('ALTER TABLE assignmentComments DROP FOREIGN KEY ', fk_name);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END LOOP;

    CLOSE fk_cursor;
END //

DELIMITER ;

-- Call the stored procedure to drop foreign keys
CALL DropForeignKeys();

-- Remove the existing primary key if it is not already set to assignmentCommentId
ALTER TABLE assignmentComments DROP PRIMARY KEY;

-- Modify assignmentCommentId to be auto_increment and set it as the primary key if it is not already
ALTER TABLE assignmentComments
    MODIFY COLUMN assignmentCommentId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY;

-- Optionally, recreate the foreign key constraints if needed
-- ALTER TABLE assignmentComments ADD CONSTRAINT fk_assignmentComments_assignments FOREIGN KEY (assignmentId) REFERENCES assignments(assignmentId);
-- ALTER TABLE assignmentComments ADD CONSTRAINT fk_assignmentComments_users FOREIGN KEY (userId) REFERENCES users(userId);