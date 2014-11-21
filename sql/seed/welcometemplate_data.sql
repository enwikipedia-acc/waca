-- MySQL dump 10.13  Distrib 5.5.40, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: production
-- ------------------------------------------------------
-- Server version	5.5.40-0ubuntu0.12.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `welcometemplate`
--

INSERT INTO `welcometemplate` VALUES (1,'{{welcome|user}} ~~~~','{{subst:Welcome|$username}}$signature');
INSERT INTO `welcometemplate` VALUES (2,'{{welcomeg|user}} ~~~~','== Welcome! ==\n\n{{subst:Welcomeg|$username|sig=$signature}}');
INSERT INTO `welcometemplate` VALUES (3,'{{welcome-personal|user}} ~~~~','{{subst:Welcome-personal|$username||$signature}}');
INSERT INTO `welcometemplate` VALUES (5,'{{WelcomeMenu|sig=~~~~}}','== Welcome! ==\n\n{{subst:WelcomeMenu|sig=$signature}}');
INSERT INTO `welcometemplate` VALUES (6,'{{WelcomeIcon}} ~~~~','== Welcome! ==\n\n{{subst:WelcomeIcon}} $signature');
INSERT INTO `welcometemplate` VALUES (7,'{{WelcomeShout|user}} ~~~~','{{subst:WelcomeShout|$username}} $signature');
INSERT INTO `welcometemplate` VALUES (8,'{{Welcomeshort|user}} ~~~~','{{subst:Welcomeshort|$username}} $signature');
INSERT INTO `welcometemplate` VALUES (9,'{{Welcomesmall|user}} ~~~~','{{subst:Welcomesmall|$username}} $signature');
INSERT INTO `welcometemplate` VALUES (13,'{{w-screen|sig=~~~~}}','== Welcome! ==\n\n{{subst:w-screen|sig=$signature}}');

INSERT INTO `welcometemplate` VALUES (28,'{{Welcome0|1=user|sig=~~~~}}','{{subst:Welcome0|1=$username|sig=$signature}}');

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-21 19:28:47
