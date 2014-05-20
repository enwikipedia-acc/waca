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
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interfacemessage`
--

LOCK TABLES `interfacemessage` WRITE;
/*!40000 ALTER TABLE `interfacemessage` DISABLE KEYS */;
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (10,'<span class =\"declined\">I\'m sorry, but the username you selected is already taken. Please try another.\r\n\r\nPlease note that Wikipedia automatically capitalizes the first letter of any user name, therefore [[User:example]] would become [[User:Example]].</span>',0,'Declined - Taken','Interface');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (11,'<span class =\"declined\">The username you chose is invalid: it consists entirely of numbers. Please retry with a valid username.</span>',0,'Declined - Numbers Only','Interface');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (12,'<span class =\"declined\">The username you chose is invalid: Your username may not be an e-mail address, which it appears to be.</span>',0,'Declined - Username Is Email','Interface');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (13,'<span class =\"declined\">The username you chose is invalid: Your username may not contain the following characters # / | [ ] { } &lt; &gt; @ % : [two consecutive blank spaces]</span>',2,'Declined - Invalid Chars','Interface');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (14,'<span class =\"declined\">Invalid E-mail address supplied. Please check you entered it correctly.</span>',0,'Declined - Invalid Email','Interface');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (16,'The system has <strong>not</strong> submitted this request.',0,'Decline - Final','Interface');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (17,'<font color=\"red\" size=\"4\">There is already an open request for this username. Please choose another.</font>',0,'Decline - Dup Request (Username)','Interface');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (18,'<font color=\"red\" size=\"4\">I\'m sorry, but you have already put in a request. Please do not submit multiple requests.</font>',0,'Decline - Dup Request (Email)','Interface');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (19,'I\'m sorry, but you are currently banned from requesting accounts using this tool. However, you can still send an email to <a href=\"mailto:accounts-enwiki-l@lists.wikimedia.org\">accounts-enwiki-l@lists.wikimedia.org</a> to request an account. \r\nThe ban on using this tool was given for the following reason: ',0,'Banned','Interface');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (20,'<script type=\"text/javascript\" language=\"JavaScript\">\r\n<!--\r\nvar cookieName = \"dismissSiteNotice=\";\r\nvar cookiePos = document.cookie.indexOf(cookieName);\r\nvar siteNoticeID = \"%SITENOTICECOUNT%\";\r\nvar siteNoticeValue=\"%SITENOTICETEXT%\";\r\nvar cookieValue = \"\";\r\nvar msgClose = \"Dismiss\";\r\nvar msgShow=\"Show sitenotice\";\r\nvar hideTitle=\"Hide sitenotice for one week\"\r\nvar showTitle=\"Show sitenotice\"\r\nfunction updateCookie(){\r\nif (cookiePos > -1) {\r\n	cookiePos = cookiePos + cookieName.length;\r\n	var endPos = document.cookie.indexOf(\";\", cookiePos);\r\n	if (endPos > -1) {\r\n		cookieValue = document.cookie.substring(cookiePos, endPos);\r\n	} else {\r\n		cookieValue = document.cookie.substring(cookiePos);\r\n	}\r\n}\r\n\r\n}\r\nupdateCookie();\r\n	function dismissNotice() {\r\n		var date = new Date();\r\n		date.setTime(date.getTime() + 7*24*60*60*1000);\r\n		var element = document.getElementById(\'innerNotice\');\r\n		var dismissButton=document.getElementById(\'dismiss\');\r\n		if(dismissButton.childNodes[0].innerHTML==\"[\"+msgClose+\"]\"){\r\n			element.style.display = \"none\";\r\n			dismissButton.childNodes[0].innerHTML=\"[\"+msgShow+\"]\";\r\n			document.getElementById(\'sitenotice\').style.paddingBottom=\"20px\";\r\n       			 document.getElementById(\'dismiss\').title=showTitle\r\n			document.cookie = cookieName + siteNoticeID + \"; expires=\"+date.toGMTString() + \"; path=/\";\r\n		}else{\r\n			element.style.display = \"block\";\r\n			dismissButton.childNodes[0].innerHTML=\"[\"+msgClose+\"]\";\r\n     			document.getElementById(\'dismiss\').title=hideTitle\r\n			document.getElementById(\'sitenotice\').style.paddingBottom=\"8px\";\r\n			document.cookie = cookieName + \"0\" + \"; expires=\"+date.toGMTString() + \"; path=/\";\r\n		}\r\nupdateCookie();\r\n	}\r\ndocument.writeln(\'	<div id=\"sitenotice\">\');\r\ndocument.writeln(\'<span title=\"\'+showTitle+\'\" id=\"dismiss\"><a href=\"javascript:dismissNotice();\">[\'+msgShow+\']</a></span>\');\r\ndocument.writeln(\'	<div id=\"innerNotice\" style=\"display:none\">\');\r\ndocument.writeln(siteNoticeValue);\r\ndocument.writeln(\'</div>\');\r\nif (cookieValue != siteNoticeID&&cookieValue != \"0\") {\r\n	document.getElementById(\'dismiss\').childNodes[0].innerHTML=\"[\"+msgClose+\"]\";\r\n        document.getElementById(\'dismiss\').title=hideTitle\r\n	document.getElementById(\'innerNotice\').style.display=\"block\";\r\n}\r\ndocument.writeln(\'	</div>\');\r\ndocument.getElementById(\'sitenotice\').style.paddingBottom=\"20px\";\r\nif (cookieValue != siteNoticeID&&cookieValue != \"0\") {\r\n	document.getElementById(\'sitenotice\').style.paddingBottom=\"8px\";\r\n}\r\n-->\r\n</script>',5,'Sitenotice code','Internal');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (27,'<span class =\"declined\">The email addresses you entered do not match. Please try again.</span>',0,'Declined - Emails do not match','interface');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (28,'<span class =\"declined\">I\'m sorry, but the username you selected is already a part of a SUL account.  Please try another username, or look at <a href=\"http://meta.wikimedia.org/wiki/Help:Unified_login\">this page</a> for more information on SUL. <br />Please note that Wikipedia automatically capitalizes the first letter of any user name, therefore [[User:example]] would become [[User:Example]].</span>',0,'Declined - SUL Taken','Interface');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (30,'Thank you for your interest in joining the English Wikipedia. At this time, we have not created your account as there are similar usernames that already exist on Wikipedia.\r\n\r\nWe have sent password reset emails to them in case you may have forgotten your password. If you do not receive any emails with a temporary password reset, it is possible that you do not own these accounts.\r\n\r\nIf that is so, please email us back and let us know.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*',0,'Password Reset','Message');
INSERT INTO `interfacemessage` (`id`, `content`, `updatecounter`, `description`, `type`) VALUES (31,'<ul><li><span style=\\\"font-weight: bold; color: red;\\\">Please remember that <u>ACC is <b><i>NOT</i></b> a race</u></span>.  Please slow down and make sure you assess each request correctly.</li><li>Please ensure you\'ve read through the <a href=\\\"http://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide\\\">documentation page</a> <i>before</i> you begin dealing with requests.</li><li>Please write emails and custom closes in plain, simple English that newcomers to Wikipedia can understand.</li><li><span style=\\\"color: blue;\\\">Please note, there will be several requests coming from IPs starting with 196 and  41, from Polytechnic of Namibia. There are supposed to be many requests coming from these IPs with \\\"Polytechnic2012\\\" in the comment line. Please approve these and do not hold them back for the purpose of mass creation</span>.  There is a conference going on at this institute. A mailing list message was sent, see it for further details.</li><li><span style=\\\"color: red;\\\">Releasing personally identifying information or placing such information in the comments is not allowed and will get you suspended.</span> Please see <a href=\\\"https://toolserver.org/~acc/other/identinfoemail.html\\\">this email</a> for further details.</li></ul>',445,'Sitenotice text','Internal');
/*!40000 ALTER TABLE `interfacemessage` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1 COMMENT='Email templates';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailtemplate`
--

LOCK TABLES `emailtemplate` WRITE;
/*!40000 ALTER TABLE `emailtemplate` DISABLE KEYS */;
INSERT INTO `emailtemplate` (`id`, `name`, `text`, `jsquestion`, `oncreated`, `active`, `preloadonly`) VALUES (1,'Created!','Many thanks for your interest in joining Wikipedia. I\'ve gone ahead and created the account for you. You will receive a separate automated e-mail from wiki@wikimedia.org with your login credentials. You can use these to log in for the first time, when you will be prompted to create a new password.\r\n\r\nWhen you have successfully logged in, you may find the &quot;getting started&quot; section of our help pages useful (http://en.wikipedia.org/wiki/Help:Contents/Getting_started). Of particular interest may be the introduction to Wikipedia (http://en.wikipedia.org/wiki/Wikipedia:Introduction) which has some information to help you get up to speed with the way things work on the encyclopedia.\r\n\r\nOne useful hint: when you have logged in for the first time and created your own password, go to your preferences (the link for them is right at the top of the screen), and ensure your email address is set where indicated. Should you forget your password, then this will allow you to have a new one sent to you!\r\n\r\nI wish you all the best and hope you enjoy your time on Wikipedia.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','Are you sure that you want to create? Please ensure you have completed all the checks.',1,1,0);
INSERT INTO `emailtemplate` (`id`, `name`, `text`, `jsquestion`, `oncreated`, `active`, `preloadonly`) VALUES (2,'Too Similar','Many thanks for your interest in joining Wikipedia. Unfortunately the username that you have requested is too similar to an active account, which may make it difficult for other contributors to distinguish you from the other user.\r\n\r\nPlease take a look at our username policy (http://en.wikipedia.org/wiki/Wikipedia:Username_policy) and choose a different username. You may be able to create the account with the new name you have chosen yourself at http://en.wikipedia.org/w/index.php?title=Special:Userlogin&amp;type=signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia.\r\n\r\nIf you are still unable to create the name yourself, we will gladly process your new request here, and I look forward to hearing from you again with your new choice of username.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','This action will send an email to the user. Are you sure that there is a conflict?',0,1,0);
INSERT INTO `emailtemplate` (`id`, `name`, `text`, `jsquestion`, `oncreated`, `active`, `preloadonly`) VALUES (3,'Taken','Many thanks for your interest in joining Wikipedia.  Unfortunately the username that you have requested is already taken.  Please choose another username (unless you had created the account yourself after requesting it).  After you have chosen another username, you may be able to create the account with the new name you have chosen yourself at http://en.wikipedia.org/w/index.php?title=Special:Userlogin&amp;type=signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia. If you are still unable to create the account yourself, we will gladly process your new request here, so feel free to submit another request.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','his action will send an email to the user. Have you checked that the account is already taken?',0,1,0);
INSERT INTO `emailtemplate` (`id`, `name`, `text`, `jsquestion`, `oncreated`, `active`, `preloadonly`) VALUES (4,'UPolicy','Many thanks for your interest in joining Wikipedia. Unfortunately the username that you have requested does not comply with our username policy, and so I am unable to create this account for you.\r\n\r\nPlease take a look at our username policy (http://en.wikipedia.org/wiki/Wikipedia:Username_policy) and choose a different username. You may be able to create the account with the new name you have chosen yourself at http://en.wikipedia.org/w/index.php?title=Special:Userlogin&amp;type=signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia.\r\n\r\nIf you are still unable to create the name yourself, we will gladly process your new request here, and I look forward to hearing from you again with your new choice of username.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','This action will send an email to the user. Are you sure that this request is a violation? Please read [[WP:UPOL]] completely before deciding.',0,1,0);
INSERT INTO `emailtemplate` (`id`, `name`, `text`, `jsquestion`, `oncreated`, `active`, `preloadonly`) VALUES (5,'Invalid','Many thanks for your interest in joining Wikipedia. Unfortunately we are unable to process your request due to technical restrictions on usernames.\r\n\r\nPlease bear in mind that it is not possible to create usernames containing any of the characters # / | [ ] { } &lt; &gt; @ % : , consisting only of numbers, or ending with an underscore ( _ ) or a space.\r\n\r\nPlease choose a username which does not contain any of these characters and then you can create an account by visiting http://en.wikipedia.org/wiki/Special:UserLogin/signup\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','This action will send an email to the user. Are you sure that the requested username is invalid?',0,1,0);
INSERT INTO `emailtemplate` (`id`, `name`, `text`, `jsquestion`, `oncreated`, `active`, `preloadonly`) VALUES (6,'SUL Taken','Many thanks for your interest in joining Wikipedia.  Unfortunately the username that you have requested is already a part of a single user login (SUL) account, meaning that someone else has reserved that name through another one of our websites.\r\n\r\nPlease choose another username (unless you had created the account yourself after requesting it), or look at http://meta.wikimedia.org/wiki/Help:Unified_login for more information on SUL.  After you have chosen another username, you may be able to create the account yourself at http://en.wikipedia.org/w/index.php?title=Special:Userlogin&amp;type=signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia. If you are still unable to create the account yourself, we will gladly process your new request here, so feel free to submit another request.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','This action will send an email to the user. Have you checked that the account is already taken?',0,1,0);
INSERT INTO `emailtemplate` (`id`, `name`, `text`, `jsquestion`, `oncreated`, `active`, `preloadonly`) VALUES (7,'Password Reset','Thank you for your interest in joining the English Wikipedia. At this time, we have not created your account as there are similar usernames that already exist on Wikipedia.\r\n\r\nWe have sent password reset emails to them in case you may have forgotten your password. If you do not receive any emails with a temporary password reset, it is possible that you do not own these accounts.\r\n\r\nIf that is so, please email us back and let us know.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','This action will send an email to the user. Are you sure that the requester owned the original reset account?',0,1,0);
/*!40000 ALTER TABLE `emailtemplate` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `acc_template`
--

LOCK TABLES `acc_template` WRITE;
/*!40000 ALTER TABLE `acc_template` DISABLE KEYS */;
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (1,'{{welcome|user}} ~~~~','{{subst:Welcome|$username}}$signature');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (2,'{{welcomeg|user}} ~~~~','== Welcome! ==\n\n{{subst:Welcomeg|$username|sig=$signature}}');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (3,'{{welcome-personal|user}} ~~~~','{{subst:Welcome-personal|$username||$signature}}');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (5,'{{WelcomeMenu|sig=~~~~}}','== Welcome! ==\n\n{{subst:WelcomeMenu|sig=$signature}}');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (6,'{{WelcomeIcon}} ~~~~','== Welcome! ==\n\n{{subst:WelcomeIcon}} $signature');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (7,'{{WelcomeShout|user}} ~~~~','{{subst:WelcomeShout|$username}} $signature');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (8,'{{Welcomeshort|user}} ~~~~','{{subst:Welcomeshort|$username}} $signature');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (9,'{{Welcomesmall|user}} ~~~~','{{subst:Welcomesmall|$username}} $signature');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (11,'{{User:Riana/Welcome|name=user|sig=~~~~}}','== Welcome! ==\n\n{{subst:User:Riana/Welcome|name=$username|sig=$signature}}');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (13,'{{w-screen|sig=~~~~}}','== Welcome! ==\n\n{{subst:w-screen|sig=$signature}}');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (15,'{{User:Malinaccier/Welcome|~~~~}}','{{subst:User:Malinaccier/Welcome|$signature}}');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (21,'{{User:Maedin/Welcome}} ~~~~','{{subst:User:Maedin/Welcome}} $signature');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (27,'{{User:Mlpearc/Accwelcome|sig=~~~~}}','{{subst:User:Mlpearc/Accwelcome|user=$username|sig=$signature}}');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (28,'{{Welcome0|1=user|sig=~~~~}}','{{subst:Welcome0|1=$username|sig=$signature}}');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (29,'{{User:Matthewrbowker/Templates/ACC Welcome}} ~~~~','{{subst:User:Matthewrbowker/Templates/ACC Welcome|user=$username|sig=$signature}}');
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES (31,'{{User:Callanecc/Template/W-graphical|2=user|sig=~~~~}}','{{subst:User:Callanecc/Template/W-graphical|user=$username|sig=$signature}}');
/*!40000 ALTER TABLE `acc_template` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-05-20  0:07:13
