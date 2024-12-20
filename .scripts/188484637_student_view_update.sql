CREATE TABLE assignmentCommentTypes
(
    assignmentCommentTypeId   INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    assignmentCommentTypeName VARCHAR(191) NOT NULL
);

INSERT INTO assignmentCommentTypes (assignmentCommentTypeName)
VALUES ('Normal comment'),
       ('Proposed solution'),
       ('Accepted solution'),
       ('Rejected solution');

ALTER TABLE assignmentComments
    ADD COLUMN assignmentCommentTypeId INT UNSIGNED NOT NULL default 1,
    ADD FOREIGN KEY (assignmentCommentTypeId) REFERENCES assignmentCommentTypes (assignmentCommentTypeId);

-- Iterate over each userAssignment record
SELECT assignmentId, userId, comments
FROM userAssignments
WHERE comments IS NOT NULL AND comments != '[]';

-- For each record, parse the JSON and insert into assignmentComments
DELIMITER $$

CREATE PROCEDURE MigrateComments() -- Define a new stored procedure named MigrateComments
BEGIN
    DECLARE done INT DEFAULT FALSE; -- Declare a variable 'done' to track the end of the cursor loop, initialized to FALSE
    DECLARE assignmentId INT; -- Declare a variable to store the assignment ID from the cursor
    DECLARE userId INT; -- Declare a variable to store the user ID from the cursor
    DECLARE comments TEXT; -- Declare a variable to store the comments from the cursor
    DECLARE cur CURSOR FOR -- Declare a cursor named 'cur' to iterate over records
        SELECT assignmentId, userId, comments
        FROM userAssignments
        WHERE comments IS NOT NULL AND comments != '[]'; -- Select records where comments are not null or empty
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE; -- Set 'done' to TRUE when no more records are found

    OPEN cur; -- Open the cursor to start fetching records

    read_loop: LOOP -- Start a loop labeled 'read_loop'
        FETCH cur INTO assignmentId, userId, comments; -- Fetch the next record into the declared variables
        IF done THEN -- Check if the end of the cursor has been reached
            LEAVE read_loop; -- Exit the loop if no more records are found
        END IF;

        -- Parse JSON and insert each comment into assignmentComments
        SET @json = comments; -- Assign the fetched comments to a session variable '@json'
        SET @assignmentId = assignmentId; -- Assign the fetched assignmentId to a session variable '@assignmentId'
        SET @userId = userId; -- Assign the fetched userId to a session variable '@userId'

        -- Use JSON_TABLE to extract data from JSON array
        INSERT INTO assignmentComments (userId, assignmentId, comment, assignmentCommentCreatedAt, assignmentCommentAuthorId) -- Insert extracted data into assignmentComments table
        SELECT 
            u.userId, -- Resolve userId from userName
            @assignmentId, -- Use the assignmentId from the session variable
            jt.comment, -- Extracted comment from JSON
            jt.createdAt, -- Extracted createdAt timestamp from JSON
            NULL -- Assuming no author ID is available in the JSON, set to NULL
        FROM JSON_TABLE(@json, '$[*]' -- Use JSON_TABLE to parse the JSON array
            COLUMNS (
                comment TEXT PATH '$.comment', -- Extract 'comment' field from JSON
                createdAt DATETIME PATH '$.createdAt', -- Extract 'createdAt' field from JSON
                name VARCHAR(191) PATH '$.name' -- Extract 'name' field from JSON
            )
        ) AS jt -- Alias the JSON_TABLE result as 'jt'
        JOIN users u ON u.userName = jt.name; -- Join with users table to resolve userId
    END LOOP; -- End of the loop

    CLOSE cur; -- Close the cursor after processing all records
END$$ -- End of the stored procedure

DELIMITER ;

-- Call the procedure to perform the migration
CALL MigrateComments();

