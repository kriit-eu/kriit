CREATE TABLE finalGrades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subjectExternalId INT NOT NULL,
  assignmentExternalId INT NOT NULL,
  assignmentName VARCHAR(255) NOT NULL,
  studentPersonalCode VARCHAR(20) NOT NULL,
  grade VARCHAR(10),
  syncedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_outcome (subjectExternalId, assignmentExternalId, studentPersonalCode)
);
