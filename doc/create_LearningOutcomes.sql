CREATE TABLE LearningOutcomes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subjectId INT NOT NULL,
  curriculumModuleOutcomes INT NOT NULL,
  nameEt VARCHAR(255) NOT NULL,
  learningOutcomeOrderNr INT DEFAULT NULL
);



/*
OLD
CREATE TABLE LearningOutcomes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subjectId INT NOT NULL,
  outcomeId INT NOT NULL,
  outcomeName VARCHAR(255) NOT NULL,
  learningOutcomeOrderNr INT DEFAULT NULL,
  syncedAt DATETIME DEFAULT CURRENT_TIMESTAMP
);
*/