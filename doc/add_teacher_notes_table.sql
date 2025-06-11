-- Create teacherNotes table for private teacher notes
CREATE TABLE `teacherNotes` (
  `noteId` int unsigned NOT NULL AUTO_INCREMENT,
  `studentId` int unsigned NOT NULL COMMENT 'Student ID',
  `assignmentId` int unsigned NOT NULL COMMENT 'Assignment ID',
  `teacherId` int unsigned NOT NULL COMMENT 'Teacher who made the note',
  `noteContent` text NOT NULL COMMENT 'The private note content',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`noteId`),
  UNIQUE KEY `idx_teacher_notes_unique` (`studentId`, `assignmentId`, `teacherId`),
  KEY `idx_teacher_notes_student` (`studentId`),
  KEY `idx_teacher_notes_assignment` (`assignmentId`),
  KEY `idx_teacher_notes_teacher` (`teacherId`),
  CONSTRAINT `fk_teacher_notes_student` FOREIGN KEY (`studentId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `fk_teacher_notes_assignment` FOREIGN KEY (`assignmentId`) REFERENCES `assignments` (`assignmentId`) ON DELETE CASCADE,
  CONSTRAINT `fk_teacher_notes_teacher` FOREIGN KEY (`teacherId`) REFERENCES `users` (`userId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
