ALTER TABLE finalGrades ADD COLUMN userId INT NULL;
-- Optionally, add a foreign key constraint if you want strict integrity:
-- ALTER TABLE finalGrades ADD FOREIGN KEY (userId) REFERENCES users(userId);
