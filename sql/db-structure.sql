-- MySQL dump 10.13  Distrib 5.5.12, for solaris10 (i386)
--
-- Host: sql    Database: p_acc_live
-- ------------------------------------------------------
-- Server version	5.1.66

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES latin1 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `acc_ban`
--

DROP TABLE IF EXISTS `acc_ban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acc_ban` (
  `ban_id` int(11) NOT NULL AUTO_INCREMENT,
  `ban_type` varchar(255) NOT NULL,
  `ban_target` varchar(700) NOT NULL,
  `ban_user` varchar(255) NOT NULL,
  `ban_reason` varchar(4096) NOT NULL,
  `ban_date` varchar(1024) NOT NULL,
  `ban_duration` varchar(255) NOT NULL,
  `ban_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ban_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3356 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acc_cmt`
--

DROP TABLE IF EXISTS `acc_cmt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acc_cmt` (
  `cmt_id` int(11) NOT NULL AUTO_INCREMENT,
  `cmt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cmt_user` varchar(255) NOT NULL,
  `cmt_comment` mediumtext NOT NULL,
  `cmt_visability` varchar(255) NOT NULL,
  `pend_id` int(11) NOT NULL,
  PRIMARY KEY (`cmt_id`),
  UNIQUE KEY `cmt_id` (`cmt_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17577 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acc_emails`
--

DROP TABLE IF EXISTS `acc_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acc_emails` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_text` blob NOT NULL,
  `mail_count` int(11) NOT NULL,
  `mail_desc` varchar(255) NOT NULL,
  `mail_type` varchar(255) NOT NULL,
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acc_log`
--

DROP TABLE IF EXISTS `acc_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acc_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_pend` varchar(255) NOT NULL,
  `log_user` varchar(255) NOT NULL,
  `log_action` varchar(255) NOT NULL,
  `log_time` datetime NOT NULL,
  `log_cmt` blob NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `acc_log_action_idx` (`log_action`),
  KEY `log_pend_idx` (`log_pend`)
) ENGINE=InnoDB AUTO_INCREMENT=142146 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acc_pend`
--

DROP TABLE IF EXISTS `acc_pend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acc_pend` (
  `pend_id` int(11) NOT NULL AUTO_INCREMENT,
  `pend_email` varchar(512) NOT NULL,
  `pend_ip` varchar(255) NOT NULL,
  `pend_name` varchar(512) NOT NULL,
  `pend_cmt` mediumtext NOT NULL,
  `pend_status` varchar(255) NOT NULL,
  `pend_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pend_checksum` varchar(256) NOT NULL,
  `pend_emailsent` varchar(10) NOT NULL,
  `pend_mailconfirm` varchar(255) NOT NULL,
  `pend_reserved` int(11) NOT NULL DEFAULT '0' COMMENT 'User ID of user who has "reserved" this request',
  `pend_useragent` blob NOT NULL COMMENT 'Useragent of the requesting web browser',
  `pend_proxyip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`pend_id`),
  KEY `acc_pend_status_mailconf` (`pend_status`,`pend_mailconfirm`),
  KEY `pend_ip_status` (`pend_ip`,`pend_mailconfirm`),
  KEY `pend_email_status` (`pend_email`,`pend_mailconfirm`),
  KEY `ft_useragent` (`pend_useragent`(512))
) ENGINE=InnoDB AUTO_INCREMENT=85511 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acc_template`
--

DROP TABLE IF EXISTS `acc_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acc_template` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_usercode` tinytext NOT NULL,
  `template_botcode` tinytext NOT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acc_titleblacklist`
--

DROP TABLE IF EXISTS `acc_titleblacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acc_titleblacklist` (
  `titleblacklist_regex` varchar(128) NOT NULL,
  `titleblacklist_casesensitive` tinyint(1) NOT NULL,
  PRIMARY KEY (`titleblacklist_regex`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acc_trustedips`
--

DROP TABLE IF EXISTS `acc_trustedips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acc_trustedips` (
  `trustedips_ipaddr` varchar(15) NOT NULL,
  PRIMARY KEY (`trustedips_ipaddr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acc_user`
--

DROP TABLE IF EXISTS `acc_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acc_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_pass` varchar(255) NOT NULL,
  `user_level` varchar(255) NOT NULL DEFAULT 'New',
  `user_onwikiname` varchar(255) NOT NULL,
  `user_welcome_sig` varchar(4096) NOT NULL DEFAULT '',
  `user_welcome_template` varchar(1024) NOT NULL DEFAULT '0',
  `user_lastactive` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_lastip` varchar(40) CHARACTER SET utf8 NOT NULL DEFAULT '0.0.0.0',
  `user_forcelogout` int(3) NOT NULL DEFAULT '0',
  `user_secure` int(11) NOT NULL DEFAULT '0',
  `user_checkuser` int(1) NOT NULL DEFAULT '0',
  `user_identified` int(1) unsigned NOT NULL DEFAULT '0',
  `user_welcome_templateid` int(11) NOT NULL DEFAULT '0',
  `user_abortpref` tinyint(4) NOT NULL DEFAULT '0',
  `user_confirmationdiff` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `I_username` (`user_name`) USING BTREE,
  UNIQUE KEY `user_onwikiname_UNIQUE` (`user_onwikiname`),
  UNIQUE KEY `user_email_UNIQUE` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=878 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acc_welcome`
--

DROP TABLE IF EXISTS `acc_welcome`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acc_welcome` (
  `welcome_id` int(11) NOT NULL AUTO_INCREMENT,
  `welcome_uid` varchar(1024) NOT NULL,
  `welcome_user` varchar(1024) NOT NULL,
  `welcome_sig` varchar(4096) NOT NULL,
  `welcome_status` varchar(96) NOT NULL,
  `welcome_pend` int(11) NOT NULL,
  `welcome_template` varchar(2048) NOT NULL,
  PRIMARY KEY (`welcome_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35062 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `closes`
--

DROP TABLE IF EXISTS `closes`;
/*!50001 DROP VIEW IF EXISTS `closes`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `closes` (
  `CONCAT("Closed ",mail_id)` varbinary(45),
  `mail_desc` varchar(255)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `createdusers`
--

DROP TABLE IF EXISTS `createdusers`;
/*!50001 DROP VIEW IF EXISTS `createdusers`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `createdusers` (
  `pend_name` varchar(512)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `id`
--

DROP TABLE IF EXISTS `id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `id` (
  `enwikiname` varchar(50) NOT NULL,
  PRIMARY KEY (`enwikiname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logactions`
--

DROP TABLE IF EXISTS `logactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logactions` (
  `action` varchar(45) NOT NULL,
  `number` int(11) DEFAULT NULL,
  PRIMARY KEY (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `closes`
--

/*!50001 DROP TABLE IF EXISTS `closes`*/;
/*!50001 DROP VIEW IF EXISTS `closes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`acc`@`%.toolserver.org` SQL SECURITY DEFINER */
/*!50001 VIEW `closes` AS select concat('Closed ',`acc_emails`.`mail_id`) AS `CONCAT("Closed ",mail_id)`,`acc_emails`.`mail_desc` AS `mail_desc` from `acc_emails` where (`acc_emails`.`mail_type` = 'Message') union select 'Closed 0' AS `Closed 0`,'Dropped' AS `Dropped` union select 'Closed custom' AS `Closed custom`,'Closed custom' AS `Closed custom` union select 'Closed custom-n' AS `Closed custom-n`,'Closed custom - Not created' AS `Closed custom - Not created` union select 'Closed custom-y' AS `Closed custom-y`,'Closed custom - Created' AS `Closed custom - Created` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `createdusers`
--

/*!50001 DROP TABLE IF EXISTS `createdusers`*/;
/*!50001 DROP VIEW IF EXISTS `createdusers`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`acc`@`%.toolserver.org` SQL SECURITY DEFINER */
/*!50001 VIEW `createdusers` AS select distinct `acc_pend`.`pend_name` AS `pend_name` from (`acc_log` join `acc_pend` on((`acc_pend`.`pend_id` = `acc_log`.`log_pend`))) where (`acc_log`.`log_action` = 'Closed 1') order by `acc_pend`.`pend_name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-12-23  3:56:29
