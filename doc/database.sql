/*!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.8-MariaDB, for osx10.19 (arm64)
--
-- Host: 127.0.0.1    Database: sisseastumine
-- ------------------------------------------------------
-- Server version	10.11.8-MariaDB

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
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb4;
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
('2024-08-12 17:59:45',111,1,1,NULL),
('2024-08-12 20:04:34',112,1,4,3),
('2024-08-12 20:09:16',113,1,4,4),
('2024-08-12 20:13:45',114,1,4,5),
('2024-08-12 20:27:31',115,1,4,6),
('2024-08-12 20:28:25',116,1,4,6),
('2024-08-12 20:30:58',117,1,4,7),
('2024-08-12 20:33:50',118,1,4,8),
('2024-08-12 20:38:51',119,1,4,9),
('2024-08-12 20:42:23',120,1,4,10),
('2024-08-12 20:47:41',121,1,4,11),
('2024-08-12 20:51:19',122,1,4,11),
('2024-08-12 20:56:40',123,1,4,12),
('2024-08-12 21:08:12',124,1,4,13),
('2024-08-12 21:12:57',125,1,4,14),
('2024-08-12 21:22:00',126,1,4,15),
('2024-08-12 21:27:56',127,1,4,16),
('2024-08-12 21:33:30',128,1,4,17),
('2024-08-12 21:53:16',129,1,4,18),
('2024-08-12 22:03:34',130,1,4,19),
('2024-08-12 22:06:11',131,1,4,20),
('2024-08-12 22:13:34',132,1,4,21),
('2024-08-12 22:17:45',133,1,4,22),
('2024-08-12 22:32:37',134,1,4,23),
('2024-08-12 22:37:00',135,1,4,24),
('2024-08-12 22:46:42',136,1,4,25),
('2024-08-12 22:49:29',137,1,4,26),
('2024-08-12 22:55:44',138,1,4,27),
('2024-08-12 23:01:12',139,1,4,28),
('2024-08-12 23:10:22',140,1,4,29),
('2024-08-12 23:29:30',141,1,4,30);
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
) ENGINE = InnoDB
  AUTO_INCREMENT = 35
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
(31,'2024-08-12 17:58:37','2024-08-12 17:58:58','Fix admin btn','henno.taht@gmail.com','bd1f7b6'),
(32,'2024-08-12 18:07:42','2024-08-12 19:44:04','As admin I can see ','Violetta Zakorzhevskaya','d3999f1'),
(33, '2024-08-12 20:14:54', '2024-08-12 20:19:39', 'Add exercises in da', 'Violetta Zakorzhevskaya', 'adbb34a'),
(34, '2024-08-13 02:09:58', '2024-08-13 02:10:11', 'AAs admin I can add', 'henno.taht@gmail.com', '423a407');
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4;
/*!50503 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exercises`
--

LOCK TABLES `exercises` WRITE;
/*!40000 ALTER TABLE `exercises` DISABLE KEYS */;
INSERT INTO `exercises` VALUES
(1,'Muuda lehe taustavärv punaseks','<ol>\n    <li>Muuda kõrvalasuva koodiredaktoriga lehe taustavärv punaseks.</li>\n    <li>Veendu, et parempoolsel eelvaatepaneelil oleks lehe taust punane.</li>\n    <li>Kui taustavärv on punane, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>\n','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 1</title>\n    <style>body {background-color: #999999}</style>\n</head>\n<body>\n    <h1>Hello world!</h1>\n</body>\n</html>','function validate() {\n    return window.getComputedStyle(document.body).backgroundColor === \'rgb(255, 0, 0)\';\n}'),
(2,'Lisa lehele nupp','<ol>\n    <li>Lisa kõrvalasuva koodiredaktoriga lehele üks nupp</li>\n    <li>Veendu, et parempoolsel eelvaatepaneelil oleks lehel vähemalt üks nupp.</li>\n    <li>Kui nupp on olemas, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Ülesanne 2</title>\n</head>\n<body>\n    <h1>Button Check</h1>\n    <p>Some text</p>\n</body>\n</html>','function validate() {\n    return document.getElementsByTagName(\'button\').length > 0;\n}\n'),
(3,'Muuda lehe fondi','<ol>\n    <li>Muuda kõrvalasuva koodiredaktoriga lehe fonti kasutades Arial\'i fondi.</li>\n    <li>Veendu, et parempoolsel eelvaatepaneelil oleks kogu leht Arial fondiga.</li>\n    <li>Kui font on Arial, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 3</title>\n    <style>body {font-family: sans-serif;}</style>\n</head>\n<body>\n    <h1>Tere maailm!</h1>\n    <p>See on lihtne HTML leht.</p>\n</body>\n</html>\n','function validate() {\n    return window.getComputedStyle(document.body).fontFamily.includes(\'Arial\');\n}\n'),
(4,'Muuda pildi laiust','<ol>\n    <li>Muuda pildi laiust 200 piksliks.</li>\n    <li>Veendu, et pildi laius oleks 200 pikslit.</li>\n    <li>Kui pildi laius on 200 pikslit, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 4</title>\n</head>\n<body>\n    <img src=\"https://via.placeholder.com/150\" alt=\"Näidispilt\">\n</body>\n</html>\n','function validate() {\n    return document.querySelector(\'img\').width === 200;\n}'),
(5,'Märgista tekstkast kohustuslikuks','<ol>\n    <li>Märgista lisatud tekstkast kohustuslikuks.</li>\n    <li>Veendu, et tekstikastil oleks atribuut \"required\".</li>\n    <li>Kui tekstikast on kohustuslik, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 5</title>\n</head>\n<body>\n    <input type=\"text\">\n</body>\n</html>\n','function validate() {\n    return document.querySelector(\'input\').required;\n}'),
(6,'Lisa lehele form e-maili väliga','<ol>\n    <li>Lisa lehele vorm ja sisesta sinna e-maili väli.</li>\n    <li>Veendu, et vormil oleks e-maili sisestamise väli.</li>\n    <li>Kui e-maili väli on lisatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 6</title>\n</head>\n<body>\n    <!-- Lisa vorm siia -->\n</body>\n</html>\n','function validate() {\n    return !!document.querySelector(\'form\') && !!document.querySelector(\'input[type=\"email\"]\');\n}'),
(7,'Muuda tekste positsiooni','<ol>\n    <li>Muuda lehe tekst keskel joondatuks (kasuta `text-align: center`).</li>\n    <li>Veendu, et kogu lehe tekst oleks keskel joondatud.</li>\n    <li>Kui tekst on keskel, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 7</title>\n    <style>body {text-align: left;}</style>\n</head>\n<body>\n    <h1>Tere maailm!</h1>\n    <p>See on lihtne HTML leht.</p>\n</body>\n</html>\n','function validate() {\n    return window.getComputedStyle(document.body).textAlign === \'center\';\n}\n'),
(8,'Lisa pilt lehele','<ol>\n    <li>Lisa lehele pilt kasutades pildi URL-i.</li>\n    <li>Veendu, et pilt oleks õigesti lehele lisatud.</li>\n    <li>Kui pilt on lisatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 8</title>\n</head>\n<body>\n    <!-- Lisa pilt siia -->\n</body>\n</html>\n','function validate() {\n    return document.querySelector(\'img\') !== null;\n}\n'),
(9,'Lisa märkeruut ja märgista valituks','<ol>\n    <li>Lisa vormile märkeruut ja märgista see vaikimisi valituks.</li>\n    <li>Veendu, et märkeruut oleks vaikimisi valitud.</li>\n    <li>Kui märkeruut on õigesti seadistatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>\n','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 9</title>\n</head>\n<body>\n    <!-- Lisa märkeruut siia -->\n</body>\n</html>\n','function validate() {\n    return document.querySelector(\'input[type=\"checkbox\"]\').checked;\n}\n'),
(10,'Lisa div \'konteiner\' klassiga','<ol>\n    <li>Lisa lehele `div` element ja anna sellele klass \"konteiner\".</li>\n    <li>Veendu, et `div` elemendil oleks klass \"konteiner\".</li>\n    <li>Kui klass on õigesti lisatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 21</title>\n</head>\n<body>\n    <!-- Lisa div siia -->\n</body>\n</html>\n','function validate() {\n    return document.querySelector(\'div.konteiner\') !== null;\n}\n'),
(11,'Lisa raadio nuppud ','<ol>\n    <li>Lisa lehele raadio nupp koos väärtusega \"Jah\" ja \"Ei\".</li>\n    <li>Veendu, et raadio nupud oleksid lisatud ja töötaksid.</li>\n    <li>Kui raadio nupud on lisatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 11</title>\n</head>\n<body>\n    <!-- Lisa raadio nupud siia -->\n</body>\n</html>\n','function validate() {\n    const radios = document.querySelectorAll(\'input[type=\"radio\"]\');\n    if (radios.length !== 2) {\n        return false;\n    }\n    const values = Array.from(radios).map(radio => radio.value);\n    return values.includes(\"Jah\") && values.includes(\"Ei\");\n}'),
(12,'Lisa rippmenüü lehele','<ol>\n    <li>Lisa vormile rippmenüü valikuvõimalustega \"Päev\", \"Kuu\" ja \"Aasta\".</li>\n    <li>Veendu, et rippmenüü oleks lisatud ja töötaks.</li>\n    <li>Kui rippmenüü on õigesti lisatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 12</title>\n</head>\n<body>\n    <!-- Lisa rippmenüü siia -->\n</body>\n</html>\n','function validate() {\n    const options = document.querySelectorAll(\'option\');\n    if (options.length !== 3) {\n        return false;\n    }\n    const values = Array.from(options).map(option => option.value);\n    return values.includes(\"Päev\") && values.includes(\"Kuu\") && values.includes(\"Aasta\");\n}\n'),
(13,'Piira sisendi pikkus','<ol>\n    <li>Lisa lehele tekstikast ja piira sisendi pikkus maksimaalselt 10 tähemärgini.</li>\n    <li>Veendu, et sisendi pikkus oleks piiratud.</li>\n    <li>Kui piirang on õigesti rakendatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 13</title>\n</head>\n<body>\n    <input type=\"text\">\n</body>\n</html>\n','function validate() {\n    return document.querySelector(\'input\').maxLength === 10;\n}'),
(14,'Lisa tabel lehele','<ol>\n    <li>Lisa lehele tabel, kus on 2 rida ja 2 veergu.</li>\n    <li>Veendu, et tabel oleks õigesti lisatud ja selles oleks 2x2 lahtrit.</li>\n    <li>Kui tabel on õigesti lisatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 14</title>\n</head>\n<body>\n    <!-- Lisa tabel siia -->\n</body>\n</html>\n','function validate() {\n    const rows = document.querySelectorAll(\'table tr\');\n    const cells = document.querySelectorAll(\'table td\');\n    return rows.length === 2 && cells.length === 4;\n}\n'),
(15,'Lisa korrektne label sisestusväli jaoks','<ol>\n    <li>Lisa korrektne label, mis on seotud nime sisestusväljaga. Kasuta selleks `for` atribuuti label-is.</li>\n    <li>Veendu, et label oleks korrektselt seotud nime sisestusväljaga.</li>\n    <li>Kui label ja sisestusväli on õigesti seotud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 15</title>\n</head>\n<body>\n    <!-- Lisa label siia -->\n    \n    <input type=\"text\" id=\"name\" name=\"name\">\n</body>\n</html>\n','function validate() {\n    const label = document.querySelector(\'label\');\n    const input = document.querySelector(\'input[type=\"text\"]\');\n    return label && input && label.getAttribute(\'for\') === input.id;\n}\n'),
(16,'Lisa nummerdamata loend lehele','<ol>\n    <li>Loo HTML-is nummerdamata loend (\'ul\' tag), mis sisaldab kolme eset: \"Punane\", \"Roheline\", \"Sinine\".</li>\n    <li>Veendu, et parempoolsel eelvaatepaneelil oleks loend õigesti kuvatud.</li>\n    <li>Kui loend on õigesti loodud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: 16</title>\n</head>\n<body>\n    <!-- Lisa loend siia -->\n</body>\n</html>\n','function validate() {\n    const listItems = document.querySelectorAll(\'ul li\');\n    const expectedItems = [\'Punane\', \'Roheline\', \'Sinine\'];\n    const itemTexts = Array.from(listItems).map(item => item.textContent.trim());\n    return listItems.length === 3 && expectedItems.every(item => itemTexts.includes(item));\n}\n'),
(17,'Muuda lingi tekst suureks täheks','<ol>\n    <li>Muuda lingi tekst \"Kliki siin\" suureks täheks (kasuta text-transform).</li>\n    <li>Veendu, et lingi tekst oleks suurtähtedega.</li>\n    <li>Kui lingi tekst on suurtähtedega, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 17</title>\n    <style>a {text-transform: none;}</style>\n</head>\n<body>\n    <a href=\"#\">Kliki siin</a>\n</body>\n</html>\n\n','function validate() {\n    return window.getComputedStyle(document.querySelector(\'a\')).textTransform === \'uppercase\';\n}\n'),
(18,'Muuda tekstiala laius','<ol>\n    <li>Muuda tekstiala laius 300 piksliks.</li>\n    <li>Veendu, et tekstiala laius oleks 300 pikslit.</li>\n    <li>Kui tekstiala laius on 300 pikslit, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne 18</title>\n    <style>\n       /* Lisa CSS siin */\n       \n    </style>\n</head>\n<body>\n    <textarea></textarea>\n</body>\n</html>\n','function validate() {\n    const computedWidth = window.getComputedStyle(document.querySelector(\'textarea\')).width;\n    return computedWidth === \'300px\';\n}\n'),
(19,'Muuda fondi värv \"highlight\" klassi jaoks','<ol>\n    <li>Muuda lehel olevate elementide, millel on klass \"highlight\", fondi värv punaseks.</li>\n    <li>Veendu, et parempoolsel eelvaatepaneelil oleksid kõik elemendid, millel on klass \"highlight\", punase fondi värviga.</li>\n    <li>Kui fondi värv on õigesti muudetud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: 19</title>\n    <style>\n        /* Lisa CSS siin */\n    </style>\n</head>\n<body>\n    <p class=\"highlight\">See tekst peab olema punane.</p>\n    <p>See tekst jääb mustaks.</p>\n    <span class=\"highlight\">Ka see tekst peab olema punane.</span>\n    <div class=\"highlight\">Punane tekst div sees.</div>\n</body>\n</html>\n','function validate() {\n    const highlightedElements = document.querySelectorAll(\'.highlight\');\n    return Array.from(highlightedElements).every(el => window.getComputedStyle(el).color === \'rgb(255, 0, 0)\');\n}'),
(20,'Muuda kastile (div element) ümarad nurgad','<ol>\n    <li>Lisa lehel olevale kastile (div element) ümarad nurgad, kasutades CSS-i omadust `border-radius`. Määra `border-radius` väärtuseks 15 pikslit.</li>\n    <li>Veendu, et parempoolsel eelvaatepaneelil oleks kastil ümarad nurgad.</li>\n    <li>Kui kasti nurgad on õigesti ümaraks tehtud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: 20</title>\n    <style>\n        /* Lisa CSS siin */\n        div.box {\n            width: 100px;\n            height: 100px;\n            background-color: lightblue;\n        }\n    </style>\n</head>\n<body>\n    <div class=\"box\"></div>\n</body>\n</html>\n','function validate() {\n    const box = document.querySelector(\'.box\');\n    const borderRadius = window.getComputedStyle(box).borderRadius;\n    return borderRadius === \'15px\';\n}'),
(21,'Muuda kastile (div element) marginaal','<ol>\n    <li>Lisa lehel olevale kastile (div element) marginaal, kasutades CSS-i omadust. Määra marginaali väärtuseks 15 pikslit ülemisele küljele, 5 pikslit paremale küljele, 6 pikslit alumisele küljele ja 7 pikslit vasakule küljele.</li>\n    <li>Veendu, et parempoolsel eelvaatepaneelil oleks kastil marginaal igal küljel vastavalt määratud väärtustele.</li>\n    <li>Kui marginaal on õigesti lisatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: 21</title>\n    <style>\n        /* Lisa CSS siin */\n        div.box {\n            width: 100px;\n            height: 100px;\n            background-color: lightblue;\n            /* Lisa marginaal siia */\n        }\n    </style>\n</head>\n<body>\n    <div class=\"box\"></div>\n</body>\n</html>\n','function validate() {\n    const box = document.querySelector(\'.box\');\n    const computedStyle = window.getComputedStyle(box);\n    \n    const topMargin = computedStyle.marginTop === \'15px\';\n    const rightMargin = computedStyle.marginRight === \'5px\';\n    const bottomMargin = computedStyle.marginBottom === \'6px\';\n    const leftMargin = computedStyle.marginLeft === \'7px\';\n    \n    return topMargin && rightMargin && bottomMargin && leftMargin;\n}\n'),
(22,'Muuda kastile (div element) sisemine polsterdus','<ol>\n    <li>Lisa lehel olevale kastile (div element) sisemine polsterdus (padding), kasutades CSS-i omadust. Määra polsterduse väärtuseks 12 pikslit ülemisele küljele, 8 pikslit paremale küljele, 10 pikslit alumisele küljele ja 14 pikslit vasakule küljele.</li>\n    <li>Veendu, et parempoolsel eelvaatepaneelil oleks kastil sisemine polsterdus igal küljel vastavalt määratud väärtustele.</li>\n    <li>Kui polsterdus on õigesti lisatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: Padding Lisamine</title>\n    <style>\n        /* Lisa CSS siin */\n        div.box {\n            width: 100px;\n            height: 100px;\n            background-color: lightcoral;\n            /* Lisa padding siia */\n        }\n    </style>\n</head>\n<body>\n    <div class=\"box\">Sisu</div>\n</body>\n</html>\n','function validate() {\n    const box = document.querySelector(\'.box\');\n    const computedStyle = window.getComputedStyle(box);\n    \n    const topPadding = computedStyle.paddingTop === \'12px\';\n    const rightPadding = computedStyle.paddingRight === \'8px\';\n    const bottomPadding = computedStyle.paddingBottom === \'10px\';\n    const leftPadding = computedStyle.paddingLeft === \'14px\';\n    \n    return topPadding && rightPadding && bottomPadding && leftPadding;\n}\n'),
(23,'Muuda elementide display omadust ','<ol>\n    <li>Muuda lehel olevaid elemente kasutades CSS-i omadust `\'display\':</li>\n    <ul>\n        <li>Peida lõik (p element) lehelt.</li>\n        <li>Muuda nupp (button element) plokk-tasandiliseks (block-level element).</li>\n    </ul>\n    <li>Veendu, et lõik oleks lehelt peidetud ja nupp oleks plokk-tasandiline element.</li>\n    <li>Kui mõlemad muutused on õigesti rakendatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: 23</title>\n    <style>\n        /* Lisa CSS siin */\n    </style>\n</head>\n<body>\n    <p>See on lõik, mida tuleb peita.</p>\n    <button>Klikka mind</button>\n</body>\n</html>\n','function validate() {\n    const paragraph = window.getComputedStyle(document.querySelector(\'p\')).display;\n    const button = window.getComputedStyle(document.querySelector(\'button\')).display;\n    \n    const isParagraphHidden = paragraph === \'none\';\n    const isButtonBlock = button === \'block\';\n    \n    return isParagraphHidden && isButtonBlock;\n}\n'),
(24,'Kasuta erinevaid CSS-i ühikuid','<ol>\n    <li>Muuda lehel olevate elementide suurust ja asukohta kasutades erinevaid CSS-i ühikuid:</li>\n    <ul>\n        <li>Määra pealkirja (`h1` element) font suuruseks 2em.</li>\n        <li>Määra kasti (`div` element) laius 50% ja kõrgus 100 pikslit (px).</li>\n    </ul>\n    <li>Veendu, et pealkirja font suurus oleks 2em ja kasti suurused oleksid vastavalt 50% laius ja 100px kõrgus.</li>\n    <li>Kui kõik muutused on õigesti rakendatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: 24</title>\n    <style>\n        /* Lisa CSS siin */\n        body {\n            margin: 0;\n            padding: 20px;\n        }\n        div {\n            background-color: lightcoral;\n        }\n    </style>\n</head>\n<body>\n    <h1>Tere tulemast!</h1>\n    <div>Sisu kastis</div>\n</body>\n</html>\n','function validate() {\n    const headingFontSize = window.getComputedStyle(document.querySelector(\'h1\')).fontSize;\n    const divWidth = window.getComputedStyle(document.querySelector(\'div\')).width;\n    const divHeight = window.getComputedStyle(document.querySelector(\'div\')).height;\n\n    // Assuming the body\'s width is 1000px for this example, 50% of 1000px is 500px\n    const isHeadingFontSizeCorrect = headingFontSize === \'32px\' || headingFontSize === \'2em\'; // 2em is generally 32px\n    const isDivWidthCorrect = divWidth === \'50%\' || divWidth === \'500px\'; // 50% of container width, let\'s assume 500px here\n    const isDivHeightCorrect = divHeight === \'100px\';\n\n    return isHeadingFontSizeCorrect && isDivWidthCorrect && isDivHeightCorrect;\n}\n'),
(25,'Kasuta Flexboxi','<ol>\n    <li>Paiguta lehel olevad kastid (div elemendid) õigesti kasutades Flexbox omadusi:</li>\n    <ul>\n        <li>Seadista konteiner (`.container`) kasutama Flexbox paigutust.</li>\n        <li>Muuda kastide paigutust nii, et nad oleksid üksteisest ühtlaselt eraldatud.</li>\n        <li>Joonda kastid vertikaalselt konteineri keskele.</li>\n    </ul>\n    <li>Veendu, et kõik kastid oleksid paigutatud õigesti ja üksteisest ühtlaselt eraldatud, ning joondatud konteineri keskele.</li>\n    <li>Kui kõik Flexbox omadused on õigesti rakendatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: 25</title>\n    <style>\n        /* Lisa CSS siin */\n        .container {\n            width: 100%;\n            height: 150px;\n            background-color: #f0f0f0;\n        }\n        .box {\n            width: 100px;\n            height: 100px;\n            background-color: lightblue;\n            margin: 10px;\n        }\n    </style>\n</head>\n<body>\n    <div class=\"container\">\n        <div class=\"box\"></div>\n        <div class=\"box\"></div>\n        <div class=\"box\"></div>\n    </div>\n</body>\n</html>\n','function validate() {\n    const container = document.querySelector(\'.container\');\n    const containerDisplay = window.getComputedStyle(container).display;\n    const justifyContent = window.getComputedStyle(container).justifyContent;\n    const alignItems = window.getComputedStyle(container).alignItems;\n\n    return containerDisplay === \'flex\' &&\n           justifyContent === \'space-between\' &&\n           alignItems === \'center\';\n}\n\n'),
(26,'Muuda kastide läbipaistvust','<ol>\n    <li>Muuda lehel olevate kastide (`div` elementide) läbipaistvust, kasutades CSS-i `opacity` omadust.</li>\n    <ul>\n        <li>Määra esimese kasti (`.box1`) läbipaistvuseks 50%.</li>\n        <li>Määra teise kasti (`.box2`) läbipaistvuseks 20%.</li>\n    </ul>\n    <li>Veendu, et kastidel oleks määratud läbipaistvused.</li>\n    <li>Kui läbipaistvused on õigesti rakendatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: 26</title>\n    <style>\n        /* Lisa CSS siin */\n        .box1, .box2 {\n            width: 100px;\n            height: 100px;\n            margin: 10px;\n            background-color: lightcoral;\n        }\n    </style>\n</head>\n<body>\n    <div class=\"box1\"></div>\n    <div class=\"box2\"></div>\n</body>\n</html>\n','function validate() {\n    const box1Opacity = window.getComputedStyle(document.querySelector(\'.box1\')).opacity;\n    const box2Opacity = window.getComputedStyle(document.querySelector(\'.box2\')).opacity;\n\n    return box1Opacity === \'0.5\' && box2Opacity === \'0.2\';\n}\n'),
(27,'Lisa loendi numbritega','<ol>\n    <li>Loo HTML-i abil nummerdatud loend, mis sisaldab kolme eset: \"Esimene\", \"Teine\" ja \"Kolmas\".</li>\n    <li>Veendu, et loend oleks korralikult nummerdatud (1, 2, 3).</li>\n    <li>Kui loend on õigesti loodud ja nummerdatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: 27</title>\n    <style></style>\n</head>\n<body>\n    <!-- Lisa nummerdatud loend siia -->\n</body>\n</html>\n','function validate() {\n    const list = document.querySelector(\'ol\');\n    const listItems = document.querySelectorAll(\'ol li\');\n\n    const correctItems = listItems.length === 3 &&\n                        listItems[0].textContent.trim() === \'Esimene\' &&\n                        listItems[1].textContent.trim() === \'Teine\' &&\n                        listItems[2].textContent.trim() === \'Kolmas\';\n\n    return list && correctItems;\n}\n'),
(28,'Kasuta HTML vorminduselementid','<ol>\n    <li>Kasutades HTML vorminduselemente, vorminda allolev lause järgmiselt:</li>\n    <ul>\n        <li>Muuda sõna \"Oluline\" rasvaseks.</li>\n        <li>Muuda sõna \"tähtis\" kursiiviks.</li>\n        <li>Lisa sõna \"veebileht\" alla joonitud tekstina.</li>\n    </ul>\n    <li>Veendu, et kõik vorminduselemendid on õigesti rakendatud ja tekst on õigesti vormindatud.</li>\n    <li>Kui tekst on õigesti vormindatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: 28</title>\n</head>\n<body>\n    <!-- Vorminda järgnev lause: -->\n    <p>See on Oluline sõnum, mis sisaldab tähtis teavet selle kohta, kuidas luua veebileht.</p>\n</body>\n</html>\n','function validate() {\n    const strongText = document.querySelector(\'strong\')?.textContent.trim() === \'Oluline\';\n    const emText = document.querySelector(\'em\')?.textContent.trim() === \'tähtis\';\n    const uText = document.querySelector(\'u\')?.textContent.trim() === \'veebileht\';\n\n    return strongText && emText && uText;\n}\n'),
(29,'Kasuta CSS järglase kombinaatori','<ol>\n    <li>Kasuta CSS-i järglase kombinaatorit, et stiliseerida kõik `<span>` elemendid, mis asuvad `<div>` elemendi sees.</li>\n    <ul>\n        <li>Määra kõikide `<div>` elementide sees olevate `<span>` elementide tekstivärv siniseks.</li>\n    </ul>\n    <li>Veendu, et ainult `<div>` elementide sees olevad `<span>` elemendid oleksid sinise tekstiga.</li>\n    <li>Kui järglase kombinaator on õigesti rakendatud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>\n<p>Kui jääd hätta, otsi abi internetist, kuid ära suhtle ega kasuta tehisaru.</p>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: 29</title>\n    <style>\n        /* Lisa CSS siin */\n    </style>\n</head>\n<body>\n    <div>\n        <span>See tekst peab olema sinine.</span>\n    </div>\n    <p>\n        <span>See tekst peab jääma mustaks.</span>\n    </p>\n</body>\n</html>\n','function validate() {\n    const divSpanColor = window.getComputedStyle(document.querySelector(\'div span\')).color;\n    const pSpanColor = window.getComputedStyle(document.querySelector(\'p span\')).color;\n\n    return divSpanColor === \'rgb(0, 0, 255)\' && pSpanColor !== \'rgb(0, 0, 255)\';\n}\n'),
(30,'Kasuta CSS kõrguse ja laiuse','<ol>\n    <li>Määra lehel olevale kastile (`div` element) kindel laius ja kõrgus, kasutades CSS-i omadusi.</li>\n    <ul>\n        <li>Määra kasti laiuseks 200 pikslit (px).</li>\n        <li>Määra kasti kõrguseks 150 pikslit (px).</li>\n    </ul>\n    <li>Veendu, et kast oleks määratud suurusega.</li>\n    <li>Kui kasti kõrgus ja laius on õigesti määratud, klõpsa nuppu \"Kontrolli lahendust\".</li>\n</ol>','<!DOCTYPE html>\n<html lang=\"et\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ülesanne: 30</title>\n    <style>\n        /* Lisa CSS siin */\n        .box {\n            background-color: lightblue;\n        }\n    </style>\n</head>\n<body>\n    <div class=\"box\"></div>\n</body>\n</html>\n','function validate() {\n    const box = document.querySelector(\'.box\');\n    const boxWidth = window.getComputedStyle(box).width;\n    const boxHeight = window.getComputedStyle(box).height;\n\n    return boxWidth === \'200px\' && boxHeight === \'150px\';\n}\n');
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
INSERT INTO `settings` VALUES ('projectVersion', '423a407'),
                              ('translationUpdateLastRun', '2024-08-13 02:10:11');
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
(36,'Are you really sure you want to remove the language %%% and destroy its translations?','existsInCode',NULL);
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
INSERT INTO `users` VALUES (1, 'Kati Maasikas', '41111111115', 1,
                            '$2y$10$vTje.ndUFKHyuotY99iYkO.2aHJUgOsy2x0RMXP1UmrTe6CQsKbtm', 0, NULL, NULL),
                           (2, 'Mati Vaarikas', '31111111114', 0,
                            '$2y$10$vTje.ndUFKHyuotY99iYkO.2aHJUgOsy2x0RMXP1UmrTe6CQsKbtm', 0, NULL, NULL),
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

-- Dump completed on 2024-08-13  2:14:00
