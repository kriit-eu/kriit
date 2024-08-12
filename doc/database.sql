/*!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.8-MariaDB, for osx10.19 (arm64)
--
-- Host: 127.0.0.1    Database: sisseastumine
-- ------------------------------------------------------
-- Server version	10.7.8-MariaDB-1:10.7.8+maria~ubu2004-log

/*!50503 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!50503 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!50503 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!50503 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

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
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activityLog`
--

LOCK TABLES `activityLog` WRITE;
/*!40000 ALTER TABLE `activityLog` DISABLE KEYS */;
INSERT INTO `activityLog` VALUES
('2024-08-08 10:05:55',1,1,1,NULL),
('2024-08-09 12:43:27',2,1,2,NULL),
('2024-08-09 12:43:42',3,1,1,NULL),
('2024-08-09 17:15:37',4,1,2,NULL),
('2024-08-09 17:15:46',5,1,1,NULL),
('2024-08-09 17:16:26',6,1,3,NULL),
('2024-08-09 17:29:10',7,1,2,NULL),
('2024-08-09 17:29:20',8,1,1,NULL),
('2024-08-09 17:35:37',9,1,2,NULL),
('2024-08-09 17:35:53',10,1,1,NULL),
('2024-08-09 17:36:30',11,1,2,NULL),
('2024-08-09 17:36:35',12,1,1,NULL),
('2024-08-09 17:36:38',13,1,3,NULL),
('2024-08-09 17:36:49',14,1,2,NULL),
('2024-08-09 17:36:56',15,1,1,NULL),
('2024-08-11 22:18:52',16,1,1,NULL),
('2024-08-11 22:18:55',17,1,3,NULL),
('2024-08-11 22:50:51',18,1,2,NULL),
('2024-08-11 22:50:56',19,1,1,NULL),
('2024-08-11 23:19:14',20,1,2,NULL),
('2024-08-11 23:19:22',21,1,1,NULL),
('2024-08-11 23:19:30',22,1,3,NULL),
('2024-08-12 00:00:52',23,1,4,1),
('2024-08-12 10:29:26',24,1,1,NULL),
('2024-08-12 10:29:31',25,1,3,NULL),
('2024-08-12 10:31:31',26,1,4,1),
('2024-08-12 10:46:05',27,1,4,1),
('2024-08-12 10:47:01',28,1,4,1),
('2024-08-12 11:41:33',29,1,2,NULL),
('2024-08-12 11:41:43',30,1,1,NULL),
('2024-08-12 11:41:45',31,1,3,NULL),
('2024-08-12 12:27:11',32,1,4,2),
('2024-08-12 12:27:20',33,1,4,1),
('2024-08-12 12:34:17',34,1,2,NULL),
('2024-08-12 12:34:37',35,1,1,NULL),
('2024-08-12 12:34:49',36,1,4,2),
('2024-08-12 12:51:39',37,1,3,NULL),
('2024-08-12 12:51:53',38,1,4,2),
('2024-08-12 12:54:09',39,1,2,NULL),
('2024-08-12 12:54:24',40,1,1,NULL),
('2024-08-12 12:54:33',41,1,2,NULL),
('2024-08-12 12:54:39',42,1,1,NULL),
('2024-08-12 12:54:49',43,1,2,NULL),
('2024-08-12 12:54:53',44,1,1,NULL),
('2024-08-12 12:55:02',45,1,2,NULL),
('2024-08-12 12:55:07',46,1,1,NULL),
('2024-08-12 12:55:32',47,1,2,NULL),
('2024-08-12 12:55:54',48,1,1,NULL),
('2024-08-12 12:56:30',49,1,4,2),
('2024-08-12 12:59:20',50,1,2,NULL),
('2024-08-12 13:03:27',51,2,1,NULL),
('2024-08-12 13:03:32',52,2,3,NULL),
('2024-08-12 13:04:18',53,2,4,1),
('2024-08-12 13:04:33',54,2,4,2),
('2024-08-12 13:05:12',55,2,2,NULL),
('2024-08-12 13:09:24',56,2,1,NULL),
('2024-08-12 13:09:39',57,2,4,2),
('2024-08-12 13:09:39',58,2,5,NULL),
('2024-08-12 13:09:39',59,2,2,NULL),
('2024-08-12 13:10:44',60,2,1,NULL),
('2024-08-12 13:10:44',61,2,5,NULL),
('2024-08-12 13:10:44',62,2,2,NULL),
('2024-08-12 13:14:20',63,1,1,NULL),
('2024-08-12 13:53:09',64,1,2,NULL),
('2024-08-12 13:53:18',65,1,1,NULL),
('2024-08-12 13:56:55',66,1,2,NULL),
('2024-08-12 13:57:06',67,1,1,NULL),
('2024-08-12 16:07:59',68,1,2,NULL),
('2024-08-12 16:08:34',69,2,1,NULL),
('2024-08-12 16:08:37',70,2,3,NULL),
('2024-08-12 16:09:00',71,2,4,2),
('2024-08-12 16:09:00',72,2,5,NULL),
('2024-08-12 16:09:01',73,2,2,NULL),
('2024-08-12 16:15:12',74,2,1,NULL),
('2024-08-12 16:15:28',75,2,4,2),
('2024-08-12 16:15:28',76,2,5,NULL),
('2024-08-12 16:15:28',77,2,2,NULL),
('2024-08-12 16:19:23',78,2,1,NULL),
('2024-08-12 16:19:23',79,2,5,NULL),
('2024-08-12 16:19:23',80,2,2,NULL),
('2024-08-12 16:21:12',81,1,1,NULL),
('2024-08-12 16:39:22',82,1,2,NULL),
('2024-08-12 16:39:55',83,2,1,NULL),
('2024-08-12 16:40:13',84,2,3,NULL),
('2024-08-12 16:40:26',85,2,4,1),
('2024-08-12 16:40:26',86,2,5,NULL),
('2024-08-12 16:40:26',87,2,2,NULL),
('2024-08-12 16:40:42',88,1,1,NULL),
('2024-08-12 16:47:59',89,1,2,NULL),
('2024-08-12 16:48:06',90,2,1,NULL),
('2024-08-12 16:48:06',91,2,5,NULL),
('2024-08-12 16:48:06',92,2,2,NULL),
('2024-08-12 16:48:20',93,2,1,NULL),
('2024-08-12 16:48:20',94,2,5,NULL),
('2024-08-12 16:48:20',95,2,2,NULL),
('2024-08-12 16:50:11',96,1,1,NULL),
('2024-08-12 16:50:35',97,1,4,2),
('2024-08-12 16:50:35',98,1,5,NULL),
('2024-08-12 16:50:35',99,1,2,NULL),
('2024-08-12 16:53:06',100,2,1,NULL),
('2024-08-12 16:53:18',101,2,4,1),
('2024-08-12 16:53:29',102,2,4,2),
('2024-08-12 16:53:29',103,2,5,NULL),
('2024-08-12 16:53:29',104,2,2,NULL),
('2024-08-12 16:53:40',105,1,1,NULL),
('2024-08-12 16:53:40',106,1,5,NULL),
('2024-08-12 16:53:40',107,1,2,NULL),
('2024-08-12 16:53:56',108,1,1,NULL),
('2024-08-12 17:59:03',109,1,5,NULL),
('2024-08-12 17:59:03',110,1,2,NULL),
('2024-08-12 17:59:45',111,1,1,NULL);
/*!40000 ALTER TABLE `activityLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applicants`
--

DROP TABLE IF EXISTS `applicants`;
/*!50503 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applicants` (
  `applicantId` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Autocreated',
  `applicantName` varchar(50) NOT NULL COMMENT 'Autocreated',
  PRIMARY KEY (`applicantId`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applicants`
--

LOCK TABLES `applicants` WRITE;
/*!40000 ALTER TABLE `applicants` DISABLE KEYS */;
INSERT INTO `applicants` VALUES
(1,'applicant #1'),
(2,'applicant #2');
/*!40000 ALTER TABLE `applicants` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4;
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
(14,'2024-08-09 18:06:01','2024-08-09 18:06:35','Initial','Violetta Zakorzhevskaya','df34b11'),
(15,'2024-08-10 21:05:41','2024-08-11 22:17:34','Refactor exercises ','henno.taht@gmail.com','25f246e'),
(16,'2024-08-12 00:38:18','2024-08-12 10:06:54','Refactor exercise v','henno.taht@gmail.com','f3b2e5c'),
(17,'2024-08-09 17:37:19','2024-08-12 10:41:54','As an applicant I c','Violetta Zakorzhevskaya','d09c2f9'),
(18,'2024-08-12 11:00:15','2024-08-12 11:04:17','Pre-test puppeteer','Violetta Zakorzhevskaya','50e2f78'),
(19,'2024-08-12 12:02:25','2024-08-12 12:26:32','Allow from all IP a','henno.taht@gmail.com','165427d'),
(20,'2024-08-12 13:12:54','2024-08-12 13:14:11','As an applicant I c','Violetta Zakorzhevskaya','0379b64'),
(21,'2024-08-12 14:05:52','2024-08-12 14:09:31','Rename eksamikomisj','henno.taht@gmail.com','9ea53cf'),
(22,'2024-08-12 14:10:31','2024-08-12 14:15:45','Update \"Alusta uut ','henno.taht@gmail.com','63fdbcf'),
(23,'2024-08-12 14:20:25','2024-08-12 14:23:23','Add Admin button','Violetta Zakorzhevskaya','1d21143'),
(24,'2024-08-12 14:27:51','2024-08-12 14:28:12','Add admin_button.ph','Violetta Zakorzhevskaya','7d44b7d'),
(25,'2024-08-12 14:59:41','2024-08-12 15:06:07','Move Admin button c','henno.taht@gmail.com','b41f80d'),
(26,'2024-08-12 16:22:26','2024-08-12 16:23:01','Add userTimeTotal','Violetta Zakorzhevskaya','574003a'),
(27,'2024-08-12 16:52:39','2024-08-12 16:53:02','Fix \"Alusta uut ses','henno.taht@gmail.com','1ea157a'),
(28,'2024-08-12 17:14:08','2024-08-12 17:50:08','Add Ranking','Violetta Zakorzhevskaya','6e0c581'),
(29,'2024-08-12 17:51:29','2024-08-12 17:55:40','Remove ranking','henno.taht@gmail.com','219029e'),
(30,'2024-08-12 17:56:19','2024-08-12 17:56:51','Add ranking to admi','Violetta Zakorzhevskaya','680ecb6'),
(31,'2024-08-12 17:58:37','2024-08-12 17:58:58','Fix admin btn','henno.taht@gmail.com','bd1f7b6');
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
INSERT INTO `exercises` VALUES
(1,'Muuda lehe taustavärv punaseks','<ol>\n    <li>Muuda kõrvalasuva koodiredaktoriga lehe taustavärv punaseks.</li>\n    <li>Veendu, et parempoolsel eelvaatepaneelil oleks lehe taust punane.</li>\n    <li>Kui taustavärv on punane, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>\n','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 1</title>\n    <style>body {background-color: #999999}</style>\n</head>\n<body>\n    <h1>Hello world!</h1>\n</body>\n</html>','function validate() {\n    return window.getComputedStyle(document.body).backgroundColor === \'rgb(255, 0, 0)\';\n}'),
(2,'Lisa lehele nupp','<ol>\n    <li>Lisa kõrvalasuva koodiredaktoriga lehele üks nupp</li>\n    <li>Veendu, et parempoolsel eelvaatepaneelil oleks lehel vähemalt üks nupp.</li>\n    <li>Kui nupp on olemas, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Button Check</title>\n</head>\n<body>\n    <h1>Button Check</h1>\n    <p>Some text</p>\n</body>\n</html>','function validate() {\n    return document.getElementsByTagName(\'button\').length > 0;\n}\n');
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
INSERT INTO `settings` VALUES
('projectVersion','bd1f7b6'),
('translationUpdateLastRun','2024-08-12 17:58:58');
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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4;
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
(36,'Are you really sure you want to remove the language %%% and destroy its translations?','existsInCode',NULL),
(38,'Applicant Name','existsInCode',NULL);
/*!40000 ALTER TABLE `translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userDoneExercises`
--

DROP TABLE IF EXISTS `userDoneExercises`;
/*!50503 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `userDoneExercises` (
  `userId` int unsigned NOT NULL,
  `exerciseId` int unsigned NOT NULL,
  PRIMARY KEY (`exerciseId`,`userId`),
  KEY `userId` (`userId`),
  CONSTRAINT `userdoneexercises_ibfk_1` FOREIGN KEY (`exerciseId`) REFERENCES `exercises` (`exerciseId`),
  CONSTRAINT `userdoneexercises_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userDoneExercises`
--

LOCK TABLES `userDoneExercises` WRITE;
/*!40000 ALTER TABLE `userDoneExercises` DISABLE KEYS */;
INSERT INTO `userDoneExercises` VALUES
(2,1),
(1,2),
(2,2);
/*!40000 ALTER TABLE `userDoneExercises` ENABLE KEYS */;
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
  `userPassword` varchar(191) NOT NULL DEFAULT '',
  `userDeleted` tinyint unsigned NOT NULL DEFAULT 0,
  `userTimeUpAt` datetime DEFAULT NULL,
  `userTimeTotal` time DEFAULT NULL,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Kati Maasikas','41111111115',1,'$2y$10$vTje.ndUFKHyuotY99iYkO.2aHJUgOsy2x0RMXP1UmrTe6CQsKbtm',0,NULL,'05:07:24'),
(2,'Mati Vaarikas','31111111114',0,'$2y$10$vTje.ndUFKHyuotY99iYkO.2aHJUgOsy2x0RMXP1UmrTe6CQsKbtm',0,'2024-08-12 17:00:13','00:13:16'),
(3,'Genor Kasak','50204305710',0,'',0,NULL,NULL),
(4,'Sten Elisson','38512232743',0,'',0,NULL,NULL),
(5,'Alicia Jemets','49912300211',0,'',0,NULL,NULL),
(6,'Kristo Kaljurand','38102020218',0,'',0,NULL,NULL),
(7,'Tanel Maasen','50411110878',0,'',0,NULL,NULL),
(8,'Klen Kert Korjus','50204237010',0,'',0,NULL,NULL),
(9,'Aleks Kudrin','39904086018',0,'',0,NULL,NULL),
(10,'Ove Kukemelk','39111210259',0,'',0,NULL,NULL),
(11,'Annabell Lohvart','49507204943',0,'',0,NULL,NULL),
(12,'Nele Metsar','49608012712',0,'',0,NULL,NULL),
(13,'Olga Morgunova','60205205230',0,'',0,NULL,NULL),
(14,'Joonas Mägi','38302146021',0,'',0,NULL,NULL),
(15,'Markus Mäll','50210212714',0,'',0,NULL,NULL),
(16,'Bert Põder','38710155767',0,'',0,NULL,NULL),
(17,'Erik Mikussaar','39610044919',0,'',0,NULL,NULL),
(18,'Kärt Semm','49302104917',0,'',0,NULL,NULL),
(19,'Koit Värat','39207104225',0,'',0,NULL,NULL),
(20,'Sigmar Treumann','39204292780',0,'',0,NULL,NULL),
(21,'Katrin Vaarask','49106082823',0,'',0,NULL,NULL),
(22,'Gert Ast','50803114916',0,'',0,NULL,NULL),
(23,'Joel-Martin Riive','50205176029',0,'',0,NULL,NULL),
(24,'Andre Park','37005315727',0,'',0,NULL,NULL),
(25,'Riho Sepp','39306210242',0,'',0,NULL,NULL),
(26,'Rasmus Aug','39409070845',0,'',0,NULL,NULL),
(27,'Liilian Lind','60709232817',0,'',0,NULL,NULL),
(28,'Gristel Aste','49201056533',0,'',0,NULL,NULL),
(29,'Indrek Sihver','50804046037',0,'',0,NULL,NULL),
(30,'Oleksandr Ovsiienko','50811250030',0,'',0,NULL,NULL),
(31,'Carl-Richard Pukk','50204016031',0,'',0,NULL,NULL),
(32,'Kaspar Loks','39104212765',0,'',0,NULL,NULL),
(33,'Eero Vallistu','39709126012',0,'',0,NULL,NULL),
(34,'Kuldar Joel Künnapas','50507024218',0,'',0,NULL,NULL),
(35,'Anastasija Hatkevitš','60804217070',0,'',0,NULL,NULL),
(36,'Arina Aleksandrova','60810012215',0,'',0,NULL,NULL),
(37,'Sander Prii','39310282738',0,'',0,NULL,NULL),
(38,'Efe Marko Güldere','50409020827',0,'',0,NULL,NULL),
(39,'Björn Johanson','50411280867',0,'',0,NULL,NULL),
(40,'Marleen Rivimets','49705275211',0,'',0,NULL,NULL),
(41,'Frank Tiisler','39611286010',0,'',0,NULL,NULL),
(42,'Brigita Kasemets','49910074220',0,'',0,NULL,NULL),
(43,'Olga Orlova','48801213712',0,'',0,NULL,NULL),
(44,'Timo Lempu','39203110022',0,'',0,NULL,NULL),
(45,'Sander Tuisk','39212032012',0,'',0,NULL,NULL),
(46,'Andreas Kirs','38703072751',0,'',0,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!50503 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!50503 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!50503 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!50503 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-08-12 18:01:48
