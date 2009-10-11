-- MySQL dump 10.13  Distrib 5.1.31, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: temp
-- ------------------------------------------------------
-- Server version	5.1.31-1ubuntu2

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
-- Table structure for table `acc_ban`
--

DROP TABLE IF EXISTS `acc_ban`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `acc_ban` (
  `ban_id` int(11) NOT NULL AUTO_INCREMENT,
  `ban_type` varchar(255) NOT NULL,
  `ban_target` varchar(700) NOT NULL,
  `ban_user` varchar(255) NOT NULL,
  `ban_reason` varchar(4096) NOT NULL,
  `ban_date` varchar(1024) NOT NULL,
  `ban_duration` varchar(255) NOT NULL,
  PRIMARY KEY (`ban_id`),
  UNIQUE KEY `ban_target` (`ban_target`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `acc_ban`
--

LOCK TABLES `acc_ban` WRITE;
/*!40000 ALTER TABLE `acc_ban` DISABLE KEYS */;
/*!40000 ALTER TABLE `acc_ban` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acc_cmt`
--

DROP TABLE IF EXISTS `acc_cmt`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `acc_cmt` (
  `cmt_id` int(11) NOT NULL AUTO_INCREMENT,
  `cmt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cmt_user` varchar(255) NOT NULL,
  `cmt_comment` mediumtext NOT NULL,
  `cmt_visability` varchar(255) NOT NULL,
  `pend_id` int(11) NOT NULL,
  PRIMARY KEY (`cmt_id`),
  UNIQUE KEY `cmt_id` (`cmt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `acc_cmt`
--

LOCK TABLES `acc_cmt` WRITE;
/*!40000 ALTER TABLE `acc_cmt` DISABLE KEYS */;
/*!40000 ALTER TABLE `acc_cmt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acc_emails`
--

DROP TABLE IF EXISTS `acc_emails`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `acc_emails` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_text` blob NOT NULL,
  `mail_count` int(11) NOT NULL,
  `mail_desc` varchar(255) NOT NULL,
  `mail_type` varchar(255) NOT NULL,
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `acc_emails`
--

LOCK TABLES `acc_emails` WRITE;
/*!40000 ALTER TABLE `acc_emails` DISABLE KEYS */;
INSERT INTO `acc_emails` VALUES (1,'Many thanks for your interest in joining Wikipedia. I\'ve gone ahead and created the account for you. You will receive a separate automated e-mail from wiki@wikimedia.org with your login credentials. You can use these to log in for the first time, when you will be prompted to create a new password.\r\n\r\nWhen you have successfully logged in, you may find the \"getting started\" section of our help pages useful (http://en.wikipedia.org/wiki/Help:Contents/Getting_started). Of particular interest may be the introduction to Wikipedia (http://en.wikipedia.org/wiki/Wikipedia:Introduction) which has some information to help you get up to speed with the way things work on the encyclopedia.\r\n\r\nI wish you all the best and hope you enjoy your time on Wikipedia.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*',0,'Account Created','Message'),(2,'Many thanks for your interest in joining Wikipedia. Unfortunately the username that you have requested is too similar to an active account, which may make it difficult for other contributors to distinguish you from the other user.\r\n\r\nPlease take a look at our username policy (http://en.wikipedia.org/wiki/Wikipedia:Username_policy) and choose a different username. You may be able to create the account yourself at http://en.wikipedia.org/w/index.php?title=Special:Userlogin&type=signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia.\r\n\r\nIf you are still unable to create the name yourself, we will gladly process your new request here, and I look forward to hearing from you again with your new choice of username.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*',0,'Too Similar','Message'),(3,'Many thanks for your interest in joining Wikipedia.  Unfortunately the username that you have requested is already taken.  Please choose another username.  After you have chosen another username, you may be able to create the account yourself at http://en.wikipedia.org/w/index.php?title=Special:Userlogin&type=signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia. If you are still unable to create the account yourself, we will gladly process your new request here, so feel free to submit another request.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*',0,'Username Taken','Message'),(4,'Many thanks for your interest in joining Wikipedia. Unfortunately the username that you have requested does not comply with our username policy, and so I am unable to create this account for you.\r\n\r\nPlease take a look at our username policy (http://en.wikipedia.org/wiki/Wikipedia:Username_policy) and choose a different username. You may be able to create the account yourself at http://en.wikipedia.org/w/index.php?title=Special:Userlogin&type=signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia.\r\n\r\nIf you are still unable to create the name yourself, we will gladly process your new request here, and I look forward to hearing from you again with your new choice of username.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*',0,'Username Policy','Message'),(5,'Many thanks for your interest in joining Wikipedia. Unfortunately we are unable to process your request due to technical restrictions on usernames.\r\n\r\nPlease bear in mind that it is not possible to create usernames containing any of the characters # / | [ ] { } < > @ % : , consisting only of numbers, or ending with an underscore ( _ ).\r\n\r\nPlease choose a username which does not contain any of these characters and then you can create an account by visiting http://en.wikipedia.org/wiki/Special:UserLogin/signup\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*',0,'Technical','Message'),(6,'			<h1>Request an account on the English Wikipedia</h1>\r\n			<h2>Welcome!</h2>\r\n			We need a few bits of information to create your account. The first is a username, and secondly, a <b>valid email address that we can send your password to</b>. If you want to leave any comments, feel free to do so. Note that if you use this form, your IP address will be recorded, and displayed to <a href=\"http://stable.toolserver.org/acc/users.php\">those who review account requests</a>. When you are done, click the \"Submit\" button. If you have difficulty using this tool, send an email containing your account request (but not password) to <a href=\"mailto:accounts-enwiki-l@lists.wikimedia.org\">accounts-enwiki-l@lists.wikimedia.org</a>, and we will try to deal with your requests that way.<br /><br />\r\n<center><big><font color=\"red\">WE DO NOT HAVE ACCESS TO EXISTING ACCOUNT DATA. If you have lost your password, please reset it using <a href=\"http://en.wikipedia.org/wiki/Special:UserLogin\">this form</a> at wikipedia.org. If you are trying to \'take over\' an account that already exists, please use <a href=\"http://en.wikipedia.org/wiki/WP:CHU/U\">\"Changing usernames/Usurpation\"</a> at wikipedia.org. We cannot do either of these things for you.</font></big></center>\r\n			<form action=\"index.php\" method=\"post\">\r\n				<div class=\"required\">\r\n					<label for=\"name\">Desired Username</label>\r\n\r\n					<input type=\"text\" name=\"name\" id=\"name\" size=\"30\" />\r\n					Case sensitive, first letter is always capitalized, you do not need to use all uppercase.  Note that this need not be your real name. Please make sure you don\'t leave any trailing spaces or underscores on your requested username.\r\n				</div>\r\n				<div class=\"required\">\r\n					<label for=\"email\">Your E-mail Address</label>\r\n					<input type=\"text\" name=\"email\" id=\"email\" size=\"30\" /> <br />\r\n<label for=\"email\">Confirm your E-mail Address</label>\r\n					<input type=\"text\" name=\"emailconfirm\" id=\"emailconfirm\" size=\"30\" />\r\n					We need this to send you your password. Without it, you will not receive your password, and will be unable to log in to your account.\r\n				</div>\r\n				<div class=\"optional\">\r\n\r\n					<label for=\"comments\">Comments (optional)</label>\r\n					<textarea id=\"comments\" name=\"comments\" rows=\"4\" cols=\"40\"></textarea>\r\n					Please do <b>NOT</b> ask for a specific password. One will be randomly created for you.\r\n				</div>\r\n				<div class=\"forminfo\">Please <u>check all the information</u> supplied above is correct, and that you have specified a <u>valid email address</u>, then click the submit button below.</div>\r\n				<div class=\"submit\">\r\n					<input type=\"submit\" value=\"Submit\" />\r\n					<input type=\"reset\" value=\"Reset\" />\r\n\r\n				</div>\r\n			</form>\r\n		</div>',0,'Request Form','Interface'),(7,'		<div id=\"footer\">\r\n			Account Creation Assistance Manager by <a href=\"team.php\">The ACC dev team</a>. <a href=\"https://jira.toolserver.org/browse/ACC\">Bugs?</a><br />\r\n			Designed by <a href=\"http://charlie.mudoo.net/\">Charlie Melbye</a>\r\n\r\n		</div>\r\n	</body>\r\n</html>',0,'Request Footer','Interface'),(8,'<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\" dir=\"ltr\">\r\n	<head>\r\n		<meta http-equiv=\"Content-type\" content=\"text/html; charset=utf-8\"/>\r\n		<title>Account Creation Assistance for the English Wikipedia - http://en.wikipedia.org/wiki/Wikipedia:Request an account</title>\r\n		<style type=\"text/css\" media=\"screen\">\r\n			@import \"style.css\";\r\n		</style>\r\n		<script type=\"text/javascript\" charset=\"utf-8\">\r\n			var dismissMessage = function(message) {\r\n				document.getElementById(message).style.display = \"none\";\r\n			}\r\n		</script>\r\n	</head>\r\n\r\n	<body id=\"body\">\r\n		<div id=\"header\">\r\n			<div id=\"header-title\">\r\n				Account Creation Assistance\r\n			</div>\r\n		</div>\r\n		<div id=\"navigation\">\r\n			<a href=\"http://en.wikipedia.org\">English Wikipedia</a> \r\n		</div>\r\n\r\n		<div id=\"content\">',0,'Request Header','Interface'),(9,'I\'m sorry, but your IP address is currently blocked on Wikipedia. Please send an email to <a href=\"mailto:unblock-en-l@lists.wikimedia.org\">unblock-en-l@lists.wikimedia.org</a> to request an account.',0,'Declined - Blocked','Interface'),(10,'<font color=\"red\" size=\"4\">I\'m sorry, but the username you selected is already taken. Please try another.\r\n\r\nPlease note that Wikipedia automatically capitalizes the first letter of any user name, therefore [[User:example]] would become [[User:Example]].</font>',0,'Declined - Taken','Interface'),(11,'The username you chose is invalid: it consists entirely of numbers. Please retry with a valid username.',0,'Declined - Numbers Only','Interface'),(12,'The username you chose is invalid: Your username may not be an e-mail address, which it appears to be.',0,'Declined - Username Is Email','Interface'),(13,'The username you chose is invalid: Your username may not contain the following characters # / | [ ] { } < > @ % :',0,'Declined - Invalid Chars','Interface'),(14,'Invalid E-mail address supplied. Please check you entered it correctly.',0,'Declined - Invalid Email','Interface'),(15,'<h1>\r\n    <span style=\"font-family: Arial\">E-Mail Confirmation required</span></h1>\r\n<span style=\"font-family: Arial\">\r\nMany thanks for your interest in joining Wikipedia. \r\n    <br />\r\n    <br />\r\n    Your request for an account will be received by a team of volunteers after you confirm your E-Mail address (check your inbox or spam folder). \r\n\r\nWe wish you all the best and hope you enjoy your time on Wikipedia.\r\n    <br />\r\n    <br />\r\n\r\nRegards, \r\n    <br />\r\n    <br />\r\n\r\nThe Wikipedia Account Creation Team</span>\r\n</div>\r\n</body>\r\n</html>',0,'Confirmation Needed','Interface'),(16,'The system has <strong>not</strong> submitted this request.',0,'Decline - Final','Interface'),(17,'There is already an open request for this username. Please choose another.',0,'Decline - Dup Request (Username)','Interface'),(18,'I\'m sorry, but you have already put in a request. Please do not submit multiple requests.',0,'Decline - Dup Request (Email)','Interface'),(19,'I\'m sorry, but you are currently banned from requesting accounts using this tool. However, you can still send an email to <a href=\"mailto:accounts-enwiki-l@lists.wikimedia.org\">accounts-enwiki-l@lists.wikimedia.org</a> to request an account. \r\nThe ban on using this tool was given for the following reason: ',0,'Banned','Interface'),(20,'<!--\r\nINSTRUCTIONS TO EDIT THE SITENOTICE:\r\n1. When changing it (not fixing), to ensure that it re appears to all those who have already dismissed it, increment varSiteNoticeID (where it is equal to n.0) by n+1\r\n2. Insert the message between the quotes in varSiteNoticeValue.\r\n\r\n***IMPORTANT***\r\n1. To use a literal quotation mark, use \\\" instead of \" to ensure it is not interpreted as the end of string character.  This includes quote marks in HTML markup.\r\n\r\nEND OF INSTRUCTIONS-->\r\n<script type=\"text/javascript\" language=\"JavaScript\">\r\n<!--\r\nvar cookieName = \"dismissSiteNotice=\";\r\nvar cookiePos = document.cookie.indexOf(cookieName);\r\nvar siteNoticeID = \"97.03\";\r\nvar siteNoticeValue=\"<ul><li style=\\\"font-weight: bold; color: red;\\\">O_O this is the sandbox! Party hats must be worn, and bugs must be fixed/patched/reported!</li></ul>\";\r\nvar cookieValue = \"\";\r\nvar msgClose = \"Dismiss\";\r\n\r\nif (cookiePos > -1) {\r\n	cookiePos = cookiePos + cookieName.length;\r\n	var endPos = document.cookie.indexOf(\";\", cookiePos);\r\n	if (endPos > -1) {\r\n		cookieValue = document.cookie.substring(cookiePos, endPos);\r\n	} else {\r\n		cookieValue = document.cookie.substring(cookiePos);\r\n	}\r\n}\r\nif (cookieValue != siteNoticeID) {\r\n	function dismissNotice() {\r\n		var date = new Date();\r\n		date.setTime(date.getTime() + 7*24*60*60*1000);\r\n		document.cookie = cookieName + siteNoticeID + \"; expires=\"+date.toGMTString() + \"; path=/\";\r\n		var element = document.getElementById(\'sitenotice\');\r\n		element.style.display = \"none\";\r\n	}\r\n	document.writeln(\'	<div id=\"sitenotice\">\');\r\n	document.writeln(\'<span title=\"Hide sitenotice for one week\" id=\"dismiss\"><a href=\"javascript:dismissNotice();\">[\'+msgClose+\']</a></span>\');\r\n	document.writeln(siteNoticeValue);\r\n	document.writeln(\'</div>\');\r\n}\r\n-->\r\n</script>',0,'Sitenotice','Internal'),(21,'<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\" dir=\"ltr\">\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n<title>English Wikipedia Internal Account Creation Interface</title>\r\n<meta name=\"author\" content=\"Charles Melbye\"/>\r\n<meta name=\"date\" content=\"2008-04-04T09:23:14-0400\"/>\r\n<meta name=\"copyright\" content=\"\"/>\r\n<meta name=\"keywords\" content=\"\"/>\r\n<meta name=\"description\" content=\"\"/>\r\n<meta name=\"ROBOTS\" content=\"NOINDEX, NOFOLLOW\"/>\r\n<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\"/>\r\n<meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\"/>\r\n<meta http-equiv=\"content-style-type\" content=\"text/css\"/>\r\n<meta http-equiv=\"expires\" content=\"0\"/>\r\n<style type=\"text/css\" media=\"screen\">\r\n	@import \"style.css\";\r\n</style>\r\n\r\n<script type=\"text/javascript\" src=\"script.js\"></script>\r\n</head>\r\n<body>\r\n		<div id=\"header\">\r\n			<div id=\"header-title\">\r\n				English Wikipedia Internal Account Creation Interface\r\n			</div>\r\n			<!-- Not until the code gets tweaked <div id=\"header-info\">\r\n				You are logged in as <a href=\"/settings\">charlie</a>\r\n			</div> -->\r\n		</div>\r\n		<div id=\"navigation\">\r\n			<a href=\"acc.php\"><b>Account Requests</b></a>\r\n			<a href=\"acc.php?action=logs\">Logs</a>\r\n                        <a href=\"users.php\">User List</a>\r\n<a href=\"acc.php?action=ban\">Ban Management</a>\r\n<a href=\"acc.php?action=messagemgmt\">Message Management</a>\r\n<a href=\"search.php\">Search</a>\r\n<a href=\"statistics.php\">Statistics</a>\r\n	                <a href=\"acc.php?action=prefs\">Preferences</a>\r\n                        <a href=\"http://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide\" target=\"_blank\">Documentation</a>\r\n</div>\r\n',0,'Header','Internal'),(22,'</div>\r\n<div id=\"footer\">Account Creation Assistance Manager by <a href=\"team.php\">The ACC dev team</a>. <a href=\"https://jira.toolserver.org/browse/ACC\">Bugs?</a><br />\r\n			Designed by <a href=\"http://charlie.mudoo.net/\">Charlie Melbye</a>\r\n</div>\r\n</body>\r\n</html>\r\n',0,'Footer (not logged in)','Internal'),(23,'<br /><br />\r\n</div>\r\n<div id=\"footer\">Account Creation Assistance Manager by <a href=\"team.php\">The ACC dev team</a>. <a href=\"https://jira.toolserver.org/browse/ACC\">Bug reports</a><span id=\"footer-version\">%VERSION%</span><br />\r\n			Designed by <a href=\"http://charlie.mudoo.net/\">Charlie Melbye</a>\r\n</div>\r\n</body>\r\n</html>\r\n',0,'Footer (logged in)','Internal'),(24,'<h1>\r\n    <span style=\"font-family: Arial\">Request submitted!</span></h1>\r\n<div style=\"font-family: Arial\">\r\n\r\nMany thanks for your interest in joining Wikipedia. \r\n    <br />\r\n    <br />\r\n    Your request for an account has been received, and will be considered by a willing team of volunteers, usually within 24 hours.<br />\r\n    <br />\r\nIf your account is created, you will receive an automated e-mail from wiki@wikimedia.org with your login credentials. You can use these to log in for the first time, then you will be prompted to chose a new password.\r\n    <br />\r\n    <br />\r\n\r\nWhile you wait, you may find it useful to read through the \"getting started\" section of our <a href=\"http://en.wikipedia.org/wiki/Help:Contents/Getting_started\">help pages</a>. Of particular interest may be the <a href=\"http://en.wikipedia.org/wiki/Wikipedia:Introduction\">Introduction to Wikipedia</a>, which has some information to help you get up to speed with the way things work on Wikipedia.\r\n    <br />\r\n    <br />\r\n\r\nWe wish you all the best and hope you enjoy your time on Wikipedia.\r\n    <br />\r\n    <br />\r\n\r\nRegards, \r\n    <br />\r\n    <br />\r\n\r\nThe Wikipedia Account Creation Team</div>\r\n</div>\r\n</body>\r\n</html>',0,'Email Confirmed','Interface'),(25,'Usernames that finish in a space or underscore are not allowed, Please choose another username.',0,'Declined - Trailing Space','Interface');
/*!40000 ALTER TABLE `acc_emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acc_log`
--

DROP TABLE IF EXISTS `acc_log`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `acc_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_pend` varchar(255) NOT NULL,
  `log_user` varchar(255) NOT NULL,
  `log_action` varchar(255) NOT NULL,
  `log_time` datetime NOT NULL,
  `log_cmt` blob NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `acc_log`
--

LOCK TABLES `acc_log` WRITE;
/*!40000 ALTER TABLE `acc_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `acc_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acc_pend`
--

DROP TABLE IF EXISTS `acc_pend`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `acc_pend` (
  `pend_id` int(11) NOT NULL AUTO_INCREMENT,
  `pend_email` varchar(512) NOT NULL,
  `pend_ip` varchar(255) NOT NULL,
  `pend_name` varchar(512) NOT NULL,
  `pend_cmt` mediumtext NOT NULL,
  `pend_status` varchar(255) NOT NULL,
  `pend_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pend_checksum` varchar(256) NOT NULL,
  `pend_emailsent` varchar(10) NOT NULL,
  `pend_mailconfirm` varchar(255) NOT NULL,
  `pend_reserved` int(11) NOT NULL DEFAULT '0' COMMENT 'User ID of user who has "reserved" this request',
  PRIMARY KEY (`pend_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `acc_pend`
--

LOCK TABLES `acc_pend` WRITE;
/*!40000 ALTER TABLE `acc_pend` DISABLE KEYS */;
/*!40000 ALTER TABLE `acc_pend` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acc_rev`
--

DROP TABLE IF EXISTS `acc_rev`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `acc_rev` (
  `rev_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `rev_msg` tinyint(4) NOT NULL,
  `rev_user` text NOT NULL,
  `rev_text` longtext NOT NULL,
  `rev_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rev_userid` int(11) DEFAULT NULL,
  PRIMARY KEY (`rev_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `acc_rev`
--

LOCK TABLES `acc_rev` WRITE;
/*!40000 ALTER TABLE `acc_rev` DISABLE KEYS */;
/*!40000 ALTER TABLE `acc_rev` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acc_user`
--

DROP TABLE IF EXISTS `acc_user`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `acc_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_pass` varchar(255) NOT NULL,
  `user_level` varchar(255) NOT NULL,
  `user_onwikiname` varchar(255) NOT NULL,
  `user_welcome` int(11) NOT NULL DEFAULT '0',
  `user_welcome_sig` varchar(4096) NOT NULL,
  `user_welcome_template` varchar(1024) NOT NULL,
  `user_lastactive` datetime NOT NULL,
  `user_lastip` varchar(40) CHARACTER SET utf8 NOT NULL,
  `user_forcelogout` int(3) NOT NULL,
  `user_secure` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `acc_user`
--

LOCK TABLES `acc_user` WRITE;
/*!40000 ALTER TABLE `acc_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `acc_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acc_welcome`
--

DROP TABLE IF EXISTS `acc_welcome`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `acc_welcome`
--

LOCK TABLES `acc_welcome` WRITE;
/*!40000 ALTER TABLE `acc_welcome` DISABLE KEYS */;
/*!40000 ALTER TABLE `acc_welcome` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-10-11  2:05:46
