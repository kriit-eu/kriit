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