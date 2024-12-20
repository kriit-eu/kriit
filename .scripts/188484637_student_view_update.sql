set foreign_key_checks=0;
drop table if exists assignmentCommentTypes;

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
drop table if exists assignmentComments;

create table assignmentComments
(
    assignmentCommentId        int unsigned           auto_increment,
    userId                     int unsigned           not null,
    assignmentId               int unsigned           not null,
    comment                    text                   null,
    assignmentCommentCreatedAt timestamp              null,
    assignmentCommentAuthorId  int unsigned           null,
    assignmentCommentTypeId    int unsigned default 1 not null,
    primary key (assignmentCommentId),
    constraint assignmentComments_assignments_assignmentId_fk
        foreign key (assignmentId) references assignments (assignmentId),
    constraint assignmentComments_ibfk_1
        foreign key (assignmentCommentTypeId) references assignmentCommentTypes (assignmentCommentTypeId),
    constraint assignmentComments_users_userId_fk
        foreign key (userId) references users (userId)
);

INSERT INTO assignmentComments (userId, assignmentId, comment, assignmentCommentCreatedAt, assignmentCommentAuthorId)
SELECT uaUserId,
       uaAssignmentId,
       comment_text,
       CASE
           WHEN created_at IS NULL OR created_at = '' OR created_at = '0000-00-00 00:00:00'
               THEN NULL
           ELSE STR_TO_DATE(created_at, '%Y-%m-%dT%H:%i:%s.%fZ')
           END as   assignmentCommentCreatedAt,
       users.userId authorId
FROM (SELECT ua.assignmentId                               uaAssignmentId,
             ua.userId                                     uaUserId,
             JSON_UNQUOTE(CONCAT('"', jt.comment, '"')) AS comment_text,
             JSON_UNQUOTE(CONCAT('"', jt.name, '"'))    AS author_name,
             jt.createdAt                               AS created_at
      FROM userAssignments ua,
           JSON_TABLE(ua.comments, '$[*]' COLUMNS (
               comment TEXT PATH '$.comment',
               name TEXT PATH '$.name',
               createdAt TEXT PATH '$.createdAt'
               )) AS jt
      ORDER BY assignmentId, userId) jt,
     users
WHERE users.userName = author_name;
