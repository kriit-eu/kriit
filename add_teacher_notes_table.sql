-- Create teacherNotes table for private teacher notes
CREATE TABLE `teacherNotes` (
  `teacherNoteId` int unsigned NOT NULL AUTO_INCREMENT,
  `userId` int unsigned NOT NULL COMMENT 'Student ID',
  `assignmentId` int unsigned NOT NULL COMMENT 'Assignment ID',
  `teacherId` int unsigned NOT NULL COMMENT 'Teacher who made the note',
  `noteContent` text NOT NULL COMMENT 'The private note content',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`teacherNoteId`),
  UNIQUE KEY `idx_teacher_notes_unique` (`userId`, `assignmentId`, `teacherId`),
  KEY `idx_teacher_notes_student` (`userId`),
  KEY `idx_teacher_notes_assignment` (`assignmentId`),
  KEY `idx_teacher_notes_teacher` (`teacherId`),
  CONSTRAINT `fk_teacher_notes_user` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `fk_teacher_notes_assignment` FOREIGN KEY (`assignmentId`) REFERENCES `assignments` (`assignmentId`) ON DELETE CASCADE,
  CONSTRAINT `fk_teacher_notes_teacher` FOREIGN KEY (`teacherId`) REFERENCES `users` (`userId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
