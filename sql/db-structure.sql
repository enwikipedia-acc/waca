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
-- Temporary table structure for view `acc_emails`
--

DROP TABLE IF EXISTS `acc_emails`;
/*!50001 DROP VIEW IF EXISTS `acc_emails`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `acc_emails` (
  `mail_id` tinyint NOT NULL,
  `mail_content` tinyint NOT NULL,
  `mail_count` tinyint NOT NULL,
  `mail_desc` tinyint NOT NULL,
  `mail_type` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

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
  `log_cmt` blob,
  PRIMARY KEY (`log_id`),
  KEY `acc_log_action_idx` (`log_action`),
  KEY `log_pend_idx` (`log_pend`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `pend_cmt` longtext NOT NULL,
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
  KEY `pend_email_status` (`pend_email`(255),`pend_mailconfirm`),
  KEY `ft_useragent` (`pend_useragent`(512)),
  KEY `ip` (`pend_ip`),
  KEY `mailconfirm` (`pend_mailconfirm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `acc_template`
--

DROP TABLE IF EXISTS `acc_template`;
/*!50001 DROP VIEW IF EXISTS `acc_template`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `acc_template` (
  `template_id` tinyint NOT NULL,
  `template_usercode` tinyint NOT NULL,
  `template_botcode` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `acc_trustedips`
--

DROP TABLE IF EXISTS `acc_trustedips`;
/*!50001 DROP VIEW IF EXISTS `acc_trustedips`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `acc_trustedips` (
  `trustedips_ipaddr` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `acc_user`
--

DROP TABLE IF EXISTS `acc_user`;
/*!50001 DROP VIEW IF EXISTS `acc_user`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `acc_user` (
  `user_id` tinyint NOT NULL,
  `user_name` tinyint NOT NULL,
  `user_email` tinyint NOT NULL,
  `user_password` tinyint NOT NULL,
  `user_level` tinyint NOT NULL,
  `user_onwikiname` tinyint NOT NULL,
  `user_welcome_sig` tinyint NOT NULL,
  `user_welcome_template` tinyint NOT NULL,
  `user_lastactive` tinyint NOT NULL,
  `user_lastip` tinyint NOT NULL,
  `user_forcelogout` tinyint NOT NULL,
  `user_secure` tinyint NOT NULL,
  `user_checkuser` tinyint NOT NULL,
  `user_identified` tinyint NOT NULL,
  `user_welcome_templateid` tinyint NOT NULL,
  `user_abortpref` tinyint NOT NULL,
  `user_confirmationdiff` tinyint NOT NULL,
  `user_emailsig` tinyint NOT NULL,
  `oauthrequesttoken` tinyint NOT NULL,
  `oauthrequestsecret` tinyint NOT NULL,
  `oauthaccesstoken` tinyint NOT NULL,
  `oauthaccesssecret` tinyint NOT NULL,
  `oauthidentitycache` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antispoofcache`
--

DROP TABLE IF EXISTS `antispoofcache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antispoofcache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data` blob NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ban`
--

DROP TABLE IF EXISTS `ban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ban` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `target` varchar(700) NOT NULL,
  `user` varchar(255) NOT NULL,
  `reason` varchar(4096) NOT NULL,
  `date` varchar(1024) NOT NULL,
  `duration` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `createdusers`
--

DROP TABLE IF EXISTS `createdusers`;
/*!50001 DROP VIEW IF EXISTS `createdusers`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `createdusers` (
  `pend_name` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

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
  `jsquestion` longtext NOT NULL COMMENT 'Question in Javascript popup presented to the user when they attempt to use this template',
  `oncreated` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 if this template is used for declined requests. 1 if it is used for accepted requests. Default 0',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 if the template should be an available option to users. Default 1',
  `preloadonly` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='Email templates';
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Geolocation cache table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `id`
--

DROP TABLE IF EXISTS `id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `id` (
  `enwikiname` varchar(50) NOT NULL,
  PRIMARY KEY (`enwikiname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='RDNS cache table';
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
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'New',
  `onwikiname` varchar(255) DEFAULT NULL,
  `welcome_sig` varchar(4096) NOT NULL DEFAULT '',
  `lastactive` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `forcelogout` int(3) NOT NULL DEFAULT '0',
  `checkuser` int(1) NOT NULL DEFAULT '0',
  `identified` int(1) unsigned NOT NULL DEFAULT '0',
  `welcome_template` int(11) DEFAULT NULL,
  `abortpref` tinyint(4) NOT NULL DEFAULT '0',
  `confirmationdiff` int(10) unsigned NOT NULL DEFAULT '0',
  `emailsig` blob NOT NULL,
  `oauthrequesttoken` varchar(45) DEFAULT NULL,
  `oauthrequestsecret` varchar(45) DEFAULT NULL,
  `oauthaccesstoken` varchar(45) DEFAULT NULL,
  `oauthaccesssecret` varchar(45) DEFAULT NULL,
  `oauthidentitycache` blob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `I_username` (`username`) USING BTREE,
  UNIQUE KEY `user_email_UNIQUE` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `welcometemplate`
--

DROP TABLE IF EXISTS `welcometemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `welcometemplate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usercode` text NOT NULL,
  `botcode` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `xfftrustcache`
--

DROP TABLE IF EXISTS `xfftrustcache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xfftrustcache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_xfftrustcache_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `acc_emails`
--

/*!50001 DROP TABLE IF EXISTS `acc_emails`*/;
/*!50001 DROP VIEW IF EXISTS `acc_emails`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50001 VIEW `acc_emails` AS select `interfacemessage`.`id` AS `mail_id`,`interfacemessage`.`content` AS `mail_content`,`interfacemessage`.`updatecounter` AS `mail_count`,`interfacemessage`.`description` AS `mail_desc`,`interfacemessage`.`type` AS `mail_type` from `interfacemessage` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `acc_template`
--

/*!50001 DROP TABLE IF EXISTS `acc_template`*/;
/*!50001 DROP VIEW IF EXISTS `acc_template`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50001 VIEW `acc_template` AS select `t`.`id` AS `template_id`,`t`.`usercode` AS `template_usercode`,`t`.`botcode` AS `template_botcode` from `welcometemplate` `t` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `acc_trustedips`
--

/*!50001 DROP TABLE IF EXISTS `acc_trustedips`*/;
/*!50001 DROP VIEW IF EXISTS `acc_trustedips`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50001 VIEW `acc_trustedips` AS select `xfftrustcache`.`id` AS `trustedips_ipaddr` from `xfftrustcache` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `acc_user`
--

/*!50001 DROP TABLE IF EXISTS `acc_user`*/;
/*!50001 DROP VIEW IF EXISTS `acc_user`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50001 VIEW `acc_user` AS select `user`.`id` AS `user_id`,`user`.`username` AS `user_name`,`user`.`email` AS `user_email`,`user`.`password` AS `user_password`,`user`.`status` AS `user_level`,`user`.`onwikiname` AS `user_onwikiname`,`user`.`welcome_sig` AS `user_welcome_sig`,'' AS `user_welcome_template`,`user`.`lastactive` AS `user_lastactive`,'0.0.0.0' AS `user_lastip`,`user`.`forcelogout` AS `user_forcelogout`,'1' AS `user_secure`,`user`.`checkuser` AS `user_checkuser`,`user`.`identified` AS `user_identified`,`user`.`welcome_template` AS `user_welcome_templateid`,`user`.`abortpref` AS `user_abortpref`,`user`.`confirmationdiff` AS `user_confirmationdiff`,`user`.`emailsig` AS `user_emailsig`,`user`.`oauthrequesttoken` AS `oauthrequesttoken`,`user`.`oauthrequestsecret` AS `oauthrequestsecret`,`user`.`oauthaccesstoken` AS `oauthaccesstoken`,`user`.`oauthaccesssecret` AS `oauthaccesssecret`,`user`.`oauthidentitycache` AS `oauthidentitycache` from `user` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

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
/*!50001 VIEW `closes` AS select concat('Closed ',cast(`emailtemplate`.`id` as char charset utf8)) AS `closes`,`emailtemplate`.`name` AS `mail_desc` from `emailtemplate` union select 'Closed 0' AS `Closed 0`,'Dropped' AS `Dropped` union select 'Closed custom' AS `Closed custom`,'Closed custom' AS `My_exp_Closed custom` union select 'Closed custom-n' AS `Closed custom-n`,'Closed custom - Not created' AS `Closed custom - Not created` union select 'Closed custom-y' AS `Closed custom-y`,'Closed custom - Created' AS `Closed custom - Created` */;
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
/*!50001 VIEW `createdusers` AS select distinct `acc_pend`.`pend_name` AS `pend_name` from (`acc_log` join `acc_pend` on((`acc_pend`.`pend_id` = `acc_log`.`log_pend`))) where (`acc_log`.`log_action` = 'Closed 1') order by `acc_pend`.`pend_name` */;
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
/*!50001 VIEW `request` AS select `acc_pend`.`pend_id` AS `id`,`acc_pend`.`pend_email` AS `email`,`acc_pend`.`pend_ip` AS `ip`,`acc_pend`.`pend_name` AS `name`,`acc_pend`.`pend_cmt` AS `comment`,`acc_pend`.`pend_status` AS `status`,`acc_pend`.`pend_date` AS `date`,`acc_pend`.`pend_checksum` AS `checksum`,`acc_pend`.`pend_emailsent` AS `emailsent`,`acc_pend`.`pend_mailconfirm` AS `emailconfirm`,`acc_pend`.`pend_reserved` AS `reserved`,`acc_pend`.`pend_useragent` AS `useragent`,`acc_pend`.`pend_proxyip` AS `forwardedip` from `acc_pend` */;
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

-- Dump completed on 2014-11-21 19:15:03
