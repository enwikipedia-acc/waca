-- MySQL dump 10.13  Distrib 5.5.37, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: acc
-- ------------------------------------------------------
-- Server version	5.5.37-0ubuntu0.14.04.1

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
-- Table structure for table `interfacemessage`
--

DROP TABLE IF EXISTS `interfacemessage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interfacemessage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` blob NOT NULL,
  `updatecounter` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
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
  `user_onwikiname` varchar(255) DEFAULT NULL,
  `user_welcome_sig` varchar(4096) NOT NULL DEFAULT '',
  `user_welcome_template` varchar(1024) NOT NULL DEFAULT '0',
  `user_lastactive` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_lastip` varchar(40) CHARACTER SET utf8 NOT NULL DEFAULT '0.0.0.0',
  `user_forcelogout` int(3) NOT NULL DEFAULT '0',
  `user_secure` int(11) NOT NULL DEFAULT '1',
  `user_checkuser` int(1) NOT NULL DEFAULT '0',
  `user_identified` int(1) unsigned NOT NULL DEFAULT '0',
  `user_welcome_templateid` int(11) NOT NULL DEFAULT '0',
  `user_abortpref` tinyint(4) NOT NULL DEFAULT '0',
  `user_confirmationdiff` int(10) unsigned NOT NULL DEFAULT '0',
  `user_emailsig` blob NOT NULL,
  `oauthrequesttoken` varchar(45) DEFAULT NULL,
  `oauthrequestsecret` varchar(45) DEFAULT NULL,
  `oauthaccesstoken` varchar(45) DEFAULT NULL,
  `oauthaccesssecret` varchar(45) DEFAULT NULL,
  `oauthidentitycache` blob,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `I_username` (`user_name`) USING BTREE,
  UNIQUE KEY `user_email_UNIQUE` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antispoofcache`
--

DROP TABLE IF EXISTS `antispoofcache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antispoofcache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `data` blob NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ban`
--

DROP TABLE IF EXISTS `ban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ban` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `target` varchar(700) NOT NULL,
  `user` varchar(255) NOT NULL,
  `reason` varchar(4096) NOT NULL,
  `date` varchar(1024) NOT NULL,
  `duration` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `closes`
--

DROP TABLE IF EXISTS `closes`;
/*!50001 DROP VIEW IF EXISTS `closes`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `closes` (
  `closes` tinyint NOT NULL,
  `mail_desc` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user` int(11) NOT NULL DEFAULT '0',
  `comment` mediumtext CHARACTER SET utf8 NOT NULL,
  `visibility` varchar(255) CHARACTER SET utf8 NOT NULL,
  `request` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `emailtemplate`
--

DROP TABLE IF EXISTS `emailtemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailtemplate` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Table key',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Email template',
  `text` blob NOT NULL COMMENT 'Text of the Email template',
  `jsquestion` mediumtext NOT NULL COMMENT 'Question in Javascript popup presented to the user when they attempt to use this template',
  `oncreated` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 if this template is used for declined requests. 1 if it is used for accepted requests. Default 0',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 if the template should be an available option to users. Default 1',
  `preloadonly` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Email templates';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geolocation`
--

DROP TABLE IF EXISTS `geolocation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geolocation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(45) NOT NULL,
  `data` blob NOT NULL,
  `creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `address_UNIQUE` (`address`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Geolocation cache table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdnscache`
--

DROP TABLE IF EXISTS `rdnscache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rdnscache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(45) NOT NULL,
  `data` blob NOT NULL,
  `creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `address_UNIQUE` (`address`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='RDNS cache table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `request`
--

DROP TABLE IF EXISTS `request`;
/*!50001 DROP VIEW IF EXISTS `request`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `request` (
  `id` tinyint NOT NULL,
  `email` tinyint NOT NULL,
  `ip` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `comment` tinyint NOT NULL,
  `status` tinyint NOT NULL,
  `date` tinyint NOT NULL,
  `checksum` tinyint NOT NULL,
  `emailsent` tinyint NOT NULL,
  `emailconfirm` tinyint NOT NULL,
  `reserved` tinyint NOT NULL,
  `useragent` tinyint NOT NULL,
  `forwardedip` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `user`
--

DROP TABLE IF EXISTS `user`;
/*!50001 DROP VIEW IF EXISTS `user`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `user` (
  `id` tinyint NOT NULL,
  `username` tinyint NOT NULL,
  `email` tinyint NOT NULL,
  `password` tinyint NOT NULL,
  `status` tinyint NOT NULL,
  `onwikiname` tinyint NOT NULL,
  `welcome_sig` tinyint NOT NULL,
  `lastactive` tinyint NOT NULL,
  `forcelogout` tinyint NOT NULL,
  `secure` tinyint NOT NULL,
  `checkuser` tinyint NOT NULL,
  `identified` tinyint NOT NULL,
  `welcome_template` tinyint NOT NULL,
  `abortpref` tinyint NOT NULL,
  `confirmationdiff` tinyint NOT NULL,
  `emailsig` tinyint NOT NULL,
  `oauthrequesttoken` tinyint NOT NULL,
  `oauthrequestsecret` tinyint NOT NULL,
  `oauthaccesstoken` tinyint NOT NULL,
  `oauthaccesssecret` tinyint NOT NULL,
  `oauthidentitycache` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `welcomequeue`
--

DROP TABLE IF EXISTS `welcomequeue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `welcomequeue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `request` int(11) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'Open',
  PRIMARY KEY (`id`)
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
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `closes` AS select concat('Closed ',cast(`emailtemplate`.`id` as char charset utf8)) AS `closes`,`emailtemplate`.`name` AS `mail_desc` from `emailtemplate` union select 'Closed 0' AS `Closed 0`,'Dropped' AS `Dropped` union select 'Closed custom' AS `Closed custom`,'Closed custom' AS `Closed custom` union select 'Closed custom-n' AS `Closed custom-n`,'Closed custom - Not created' AS `Closed custom - Not created` union select 'Closed custom-y' AS `Closed custom-y`,'Closed custom - Created' AS `Closed custom - Created` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `request`
--

/*!50001 DROP TABLE IF EXISTS `request`*/;
/*!50001 DROP VIEW IF EXISTS `request`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `request` AS select `acc_pend`.`pend_id` AS `id`,`acc_pend`.`pend_email` AS `email`,`acc_pend`.`pend_ip` AS `ip`,`acc_pend`.`pend_name` AS `name`,`acc_pend`.`pend_cmt` AS `comment`,`acc_pend`.`pend_status` AS `status`,`acc_pend`.`pend_date` AS `date`,`acc_pend`.`pend_checksum` AS `checksum`,`acc_pend`.`pend_emailsent` AS `emailsent`,`acc_pend`.`pend_mailconfirm` AS `emailconfirm`,`acc_pend`.`pend_reserved` AS `reserved`,`acc_pend`.`pend_useragent` AS `useragent`,`acc_pend`.`pend_proxyip` AS `forwardedip` from `acc_pend` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `user`
--

/*!50001 DROP TABLE IF EXISTS `user`*/;
/*!50001 DROP VIEW IF EXISTS `user`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `user` AS select `acc_user`.`user_id` AS `id`,`acc_user`.`user_name` AS `username`,`acc_user`.`user_email` AS `email`,`acc_user`.`user_pass` AS `password`,`acc_user`.`user_level` AS `status`,`acc_user`.`user_onwikiname` AS `onwikiname`,`acc_user`.`user_welcome_sig` AS `welcome_sig`,`acc_user`.`user_lastactive` AS `lastactive`,`acc_user`.`user_forcelogout` AS `forcelogout`,`acc_user`.`user_secure` AS `secure`,`acc_user`.`user_checkuser` AS `checkuser`,`acc_user`.`user_identified` AS `identified`,`acc_user`.`user_welcome_templateid` AS `welcome_template`,`acc_user`.`user_abortpref` AS `abortpref`,`acc_user`.`user_confirmationdiff` AS `confirmationdiff`,`acc_user`.`user_emailsig` AS `emailsig`,`acc_user`.`oauthrequesttoken` AS `oauthrequesttoken`,`acc_user`.`oauthrequestsecret` AS `oauthrequestsecret`,`acc_user`.`oauthaccesstoken` AS `oauthaccesstoken`,`acc_user`.`oauthaccesssecret` AS `oauthaccesssecret`,`acc_user`.`oauthidentitycache` AS `oauthidentitycache` from `acc_user` */;
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

-- Dump completed on 2014-05-19 23:31:31
