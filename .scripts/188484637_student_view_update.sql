/* Mariadb 10.5.12 */
set foreign_key_checks = 0;

drop table if exists assignmentComments;
create table assignmentComments
(
    assignmentCommentId        int unsigned auto_increment,
    userId                     int unsigned           not null,
    assignmentId               int unsigned           not null,
    assignmentCommentText      text                   null,
    assignmentCommentCreatedAt timestamp              null,
    assignmentCommentAuthorId  int unsigned           null,
    assignmentCommentIsProposedSolution    tinyint unsigned default 0 not null,
    primary key (assignmentCommentId),
    constraint assignmentComments_assignments_assignmentId_fk
        foreign key (assignmentId) references assignments (assignmentId),
    constraint assignmentComments_users_userId_fk
        foreign key (userId) references users (userId)
);

INSERT INTO assignmentComments (userId, assignmentId, assignmentCommentText, assignmentCommentCreatedAt, assignmentCommentAuthorId)
SELECT uaUserId,
       uaAssignmentId,
       comment_text,
       CASE
           WHEN created_at IS NULL
               OR created_at = ''
               OR created_at = '0000-00-00 00:00:00'
               OR created_at = '1970-01-01 00:00:00'
               THEN NULL
           ELSE STR_TO_DATE(created_at, '%Y-%m-%d %H:%i:%s')
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

