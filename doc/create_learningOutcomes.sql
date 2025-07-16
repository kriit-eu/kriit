CREATE TABLE learningOutcomes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subjectId INT NOT NULL,
  curriculumModuleOutcomes INT NOT NULL,
  nameEt VARCHAR(255) NOT NULL,
  learningOutcomeOrderNr INT DEFAULT NULL
);