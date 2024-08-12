/*!999999\- enable the sandbox mode */
-- MariaDB dump 10.19-11.4.2-MariaDB, for osx10.19 (arm64)
--
-- Host: 127.0.0.1    Database: sisseastumine
-- ------------------------------------------------------
-- Server version	11.4.2-MariaDB

/*!50503 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!50503 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!50503 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!50503 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;
/*!50503 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activities` (
  `activityId` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Autocreated',
  `activityName` varchar(50) NOT NULL COMMENT 'Autocreated',
  `activityDescription` varchar(191) NOT NULL,
  PRIMARY KEY (`activityId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activities`
--

LOCK TABLES `activities` WRITE;
/*!40000 ALTER TABLE `activities` DISABLE KEYS */;
INSERT INTO `activities` VALUES
(1,'login','logged in'),
(2,'logout','logged out'),
(3,'startTime','user has started the timer');
/*!40000 ALTER TABLE `activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activityLog`
--

DROP TABLE IF EXISTS `activityLog`;
/*!50503 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activityLog` (
  `activityLogTimestamp` datetime NOT NULL,
  `activityLogId` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Autocreated',
  `userId` int unsigned NOT NULL,
  `activityId` int unsigned NOT NULL COMMENT 'Autocreated',
  `id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`activityLogId`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 24
  DEFAULT CHARSET = utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activityLog`
--

LOCK TABLES `activityLog` WRITE;
/*!40000 ALTER TABLE `activityLog` DISABLE KEYS */;
INSERT INTO `activityLog` VALUES ('2024-08-08 10:05:55', 1, 1, 1, NULL),
                                 ('2024-08-09 12:43:27', 2, 1, 2, NULL),
                                 ('2024-08-09 12:43:42', 3, 1, 1, NULL),
                                 ('2024-08-09 17:15:37', 4, 1, 2, NULL),
                                 ('2024-08-09 17:15:46', 5, 1, 1, NULL),
                                 ('2024-08-09 17:16:26', 6, 1, 3, NULL),
                                 ('2024-08-09 17:29:10', 7, 1, 2, NULL),
                                 ('2024-08-09 17:29:20', 8, 1, 1, NULL),
                                 ('2024-08-09 17:35:37', 9, 1, 2, NULL),
                                 ('2024-08-09 17:35:53', 10, 1, 1, NULL),
                                 ('2024-08-09 17:36:30', 11, 1, 2, NULL),
                                 ('2024-08-09 17:36:35', 12, 1, 1, NULL),
                                 ('2024-08-09 17:36:38', 13, 1, 3, NULL),
                                 ('2024-08-09 17:36:49', 14, 1, 2, NULL),
                                 ('2024-08-09 17:36:56', 15, 1, 1, NULL),
                                 ('2024-08-11 22:18:52', 16, 1, 1, NULL),
                                 ('2024-08-11 22:18:55', 17, 1, 3, NULL),
                                 ('2024-08-11 22:50:51', 18, 1, 2, NULL),
                                 ('2024-08-11 22:50:56', 19, 1, 1, NULL),
                                 ('2024-08-11 23:19:14', 20, 1, 2, NULL),
                                 ('2024-08-11 23:19:22', 21, 1, 1, NULL),
                                 ('2024-08-11 23:19:30', 22, 1, 3, NULL),
                                 ('2024-08-12 00:00:52', 23, 1, 4, 1);
/*!40000 ALTER TABLE `activityLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deployments`
--

DROP TABLE IF EXISTS `deployments`;
/*!50503 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deployments` (
  `deploymentId` int unsigned NOT NULL AUTO_INCREMENT,
  `deploymentCommitDate` datetime NOT NULL,
  `deploymentDate` datetime NOT NULL,
  `deploymentCommitMessage` varchar(765) NOT NULL,
  `deploymentCommitAuthor` varchar(255) DEFAULT NULL,
  `deploymentCommitSha` varchar(256) NOT NULL,
  PRIMARY KEY (`deploymentId`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 16
  DEFAULT CHARSET = utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deployments`
--

LOCK TABLES `deployments` WRITE;
/*!40000 ALTER TABLE `deployments` DISABLE KEYS */;
INSERT INTO `deployments` VALUES
(2,'2024-08-07 17:44:09','2024-08-07 17:44:39','Update db','Violetta Zakorzhevskaya','0cc2dd0'),
(3,'2024-08-07 17:50:51','2024-08-08 10:01:24','Fix halo','Violetta Zakorzhevskaya','1f025bd'),
(4,'2024-08-08 10:45:54','2024-08-08 10:52:43','Fix halo','Violetta Zakorzhevskaya','f012825'),
(5,'2024-08-09 10:20:46','2024-08-09 10:22:08','Fix halo and exerci','Violetta Zakorzhevskaya','d83511e'),
(6,'2024-08-09 10:40:54','2024-08-09 10:41:18','Fix \"Data too long ','henno.taht@gmail.com','3710630'),
(7,'2024-08-09 10:57:59','2024-08-09 10:59:04','Add excercise layou','henno.taht@gmail.com','941dd2c'),
(8,'2024-08-09 13:03:28','2024-08-09 13:14:32','As an applicant I c','Violetta Zakorzhevskaya','76eb006'),
(9,'2024-08-09 14:59:43','2024-08-09 15:03:56','Refactor timer func','henno.taht@gmail.com','4a8aaa8'),
(10,'2024-08-09 15:11:52','2024-08-09 15:13:38','Enhance time-up log','henno.taht@gmail.com','10f8d03'),
(11,'2024-08-07 15:40:08','2024-08-09 17:15:37','As an applicant I c','Violetta Zakorzhevskaya','91f457d'),
(12,'2024-08-09 17:28:34','2024-08-09 17:28:48','As an applicant I c','Violetta Zakorzhevskaya','f1f18d1'),
(13,'2024-08-09 17:37:19','2024-08-09 17:44:50','As an applicant I c','Violetta Zakorzhevskaya','d09c2f9'),
(14, '2024-08-09 18:06:01', '2024-08-09 18:06:35', 'Initial', 'Violetta Zakorzhevskaya', 'df34b11'),
(15, '2024-08-10 21:05:41', '2024-08-11 22:17:34', 'Refactor exercises ', 'henno.taht@gmail.com', '25f246e');
/*!40000 ALTER TABLE `deployments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exercises`
--

DROP TABLE IF EXISTS `exercises`;
/*!50503 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exercises` (
  `exerciseId` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Autocreated',
  `exerciseName` varchar(50) NOT NULL COMMENT 'Autocreated',
  `exerciseInstructions` text,
  `exerciseInitialCode` text,
  `exerciseValidationFunction` text,
  PRIMARY KEY (`exerciseId`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exercises`
--

LOCK TABLES `exercises` WRITE;
/*!40000 ALTER TABLE `exercises` DISABLE KEYS */;
INSERT INTO `exercises` VALUES (1, 'Muuda lehe taustavärv punaseks',
                                '<ol>\n    <li>Muuda kõrvalasuva koodiredaktoriga lehe taustavärv punaseks.</li>\n    <li>Veendu, et parempoolsel eelvaatepaneelil oleks lehe taust punane.</li>\n    <li>Kui taustavärv on punane, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>\n',
                                '<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 1</title>\n    <style>body {background-color: #999999}</style>\n</head>\n<body>\n    <h1>Hello world!</h1>\n</body>\n</html>',
                                'function validate() {\n    return window.getComputedStyle(document.body).backgroundColor === \'rgb(255, 0, 0)\';\n}'),
                               (2, 'exercise #2', NULL, NULL, NULL);
/*!40000 ALTER TABLE `exercises` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!50503 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `settingName` varchar(255) NOT NULL,
  `settingValue` varchar(765) DEFAULT NULL,
  PRIMARY KEY (`settingName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('projectVersion', '25f246e'),
                              ('translationUpdateLastRun', '2024-08-11 22:17:34');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translationLanguages`
--

DROP TABLE IF EXISTS `translationLanguages`;
/*!50503 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `translationLanguages` (
  `translationLanguageCode` varchar(255) NOT NULL,
  `translationLanguageName` varchar(255) NOT NULL,
  PRIMARY KEY (`translationLanguageCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translationLanguages`
--

LOCK TABLES `translationLanguages` WRITE;
/*!40000 ALTER TABLE `translationLanguages` DISABLE KEYS */;
INSERT INTO `translationLanguages` VALUES
('af','Afrikaans'),
('am','Amharic'),
('ar','Arabic'),
('az','Azerbaijani'),
('be','Belarusian'),
('bg','Bulgarian'),
('bn','Bengali'),
('bs','Bosnian'),
('ca','Catalan'),
('ceb','Cebuano'),
('co','Corsican'),
('cs','Czech'),
('cy','Welsh'),
('da','Danish'),
('de','German'),
('el','Greek'),
('en','English'),
('eo','Esperanto'),
('es','Spanish'),
('et','Estonian'),
('eu','Basque'),
('fa','Persian'),
('fi','Finnish'),
('fr','French'),
('fy','Frisian'),
('ga','Irish'),
('gd','Scots Gaelic'),
('gl','Galician'),
('gu','Gujarati'),
('ha','Hausa'),
('haw','Hawaiian'),
('he','Hebrew'),
('hi','Hindi'),
('hmn','Hmong'),
('hr','Croatian'),
('ht','Haitian'),
('hu','Hungarian'),
('hy','Armenian'),
('id','Indonesian'),
('ig','Igbo'),
('is','Icelandic'),
('it','Italian'),
('ja','Japanese'),
('jv','Javanese'),
('ka','Georgian'),
('kk','Kazakh'),
('km','Khmer'),
('kn','Kannada'),
('ko','Korean'),
('ku','Kurdish'),
('ky','Kyrgyz'),
('la','Latin'),
('lb','Luxembourgish'),
('lo','Lao'),
('lt','Lithuanian'),
('lv','Latvian'),
('mg','Malagasy'),
('mi','Maori'),
('mk','Macedonian'),
('ml','Malayalam'),
('mn','Mongolian'),
('mr','Marathi'),
('ms','Malay'),
('mt','Maltese'),
('my','Myanmar'),
('ne','Nepali'),
('nl','Dutch'),
('no','Norwegian'),
('ny','Nyanja (Chichewa)'),
('or','Odia (Oriya)'),
('pa','Punjabi'),
('pl','Polish'),
('ps','Pashto'),
('pt','Portuguese'),
('ro','Romanian'),
('ru','Russian'),
('rw','Kinyarwanda'),
('sd','Sindhi'),
('si','Sinhala (Sinhalese)'),
('sk','Slovak'),
('sl','Slovenian'),
('sm','Samoan'),
('sn','Shona'),
('so','Somali'),
('sq','Albanian'),
('sr','Serbian'),
('st','Sesotho'),
('su','Sundanese'),
('sv','Swedish'),
('sw','Swahili'),
('ta','Tamil'),
('te','Telugu'),
('tg','Tajik'),
('th','Thai'),
('tk','Turkmen'),
('tl','Tagalog (Filipino)'),
('tr','Turkish'),
('tt','Tatar'),
('ug','Uyghur'),
('uk','Ukrainian'),
('ur','Urdu'),
('uz','Uzbek'),
('vi','Vietnamese'),
('xh','Xhosa'),
('yi','Yiddish'),
('yo','Yoruba'),
('zh','Chinese'),
('zu','Zulu');
/*!40000 ALTER TABLE `translationLanguages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
/*!50503 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `translations` (
  `translationId` int unsigned NOT NULL AUTO_INCREMENT,
  `translationPhrase` varchar(765) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `translationState` varchar(255) NOT NULL DEFAULT 'existsInCode',
  `TranslationSource` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`translationId`),
  UNIQUE KEY `translations_translationPhrase_uindex` (`translationPhrase`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translations`
--

LOCK TABLES `translations` WRITE;
/*!40000 ALTER TABLE `translations` DISABLE KEYS */;
INSERT INTO `translations` VALUES
(1,'Invalid username','existsInCode',NULL),
(2,'Invalid password','existsInCode',NULL),
(3,'User already exists','existsInCode',NULL),
(4,'You cannot delete yourself','existsInCode',NULL),
(5,'Server returned response in an unexpected format','existsInCode',NULL),
(6,'Forbidden','existsInCode',NULL),
(7,'Server returned an error. Please try again later','existsInCode',NULL),
(8,'Module Name','existsInCode',NULL),
(9,'Access denied','existsInCode',NULL),
(10,'Users','existsInCode',NULL),
(11,'Logs','existsInCode',NULL),
(12,'Halo','existsInCode',NULL),
(13,'Translations','existsInCode',NULL),
(14,'Logout','existsInCode',NULL),
(15,'Error','existsInCode',NULL),
(16,'Unknown error!','existsInCode',NULL),
(17,'Time','existsInCode',NULL),
(18,'User','existsInCode',NULL),
(19,'Activity','existsInCode',NULL),
(20,'Name','existsInCode',NULL),
(21,'Email','existsInCode',NULL),
(22,'Password','existsInCode',NULL),
(23,'Edit user','existsInCode',NULL),
(24,'Admin','existsInCode',NULL),
(25,'Set to 1 if user must be admin','existsInCode',NULL),
(26,'Save changes','existsInCode',NULL),
(27,'Close','existsInCode',NULL),
(28,'Are you sure?','existsInCode',NULL),
(29,'Phrase','existsInCode',NULL),
(30,'Untranslated','existsInCode',NULL),
(31,'Search','existsInCode',NULL),
(32,'Languages','existsInCode',NULL),
(33,'Select language','existsInCode',NULL),
(34,'Google translates < 5000 chars at a time','existsInCode',NULL),
(35,'Select language first','existsInCode',NULL),
(36,'Are you really sure you want to remove the language %%% and destroy its translations?','existsInCode',NULL);
/*!40000 ALTER TABLE `translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userDoneExercises`
--

DROP TABLE IF EXISTS `userDoneExercises`;
/*!50503 SET @saved_cs_client = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `userDoneExercises`
(
    `userId`     int unsigned NOT NULL,
    `exerciseId` int unsigned NOT NULL,
    PRIMARY KEY (`exerciseId`, `userId`),
    KEY `userId` (`userId`),
    CONSTRAINT `userdoneexercises_ibfk_1` FOREIGN KEY (`exerciseId`) REFERENCES `exercises` (`exerciseId`),
    CONSTRAINT `userdoneexercises_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userDoneExercises`
--

LOCK TABLES `userDoneExercises` WRITE;
/*!40000 ALTER TABLE `userDoneExercises`
    DISABLE KEYS */;
/*!40000 ALTER TABLE `userDoneExercises`
    ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!50503 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `userId` int unsigned NOT NULL AUTO_INCREMENT,
  `userName` varchar(191) NOT NULL,
  `userPersonalCode` varchar(191) NOT NULL,
  `userIsAdmin` tinyint NOT NULL DEFAULT 0,
  `userPassword` varchar(191) NOT NULL,
  `userDeleted` tinyint unsigned NOT NULL DEFAULT 0,
  `userTimeUpAt` datetime DEFAULT NULL,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1, 'Demo User', '41111111115', 0,
                            '$2y$10$vTje.ndUFKHyuotY99iYkO.2aHJUgOsy2x0RMXP1UmrTe6CQsKbtm', 0, NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!50503 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!50503 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!50503 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!50503 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2024-08-12  0:01:45
