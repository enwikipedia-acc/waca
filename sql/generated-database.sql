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
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `acc_log` (
  `log_id` tinyint NOT NULL,
  `log_pend` tinyint NOT NULL,
  `log_user` tinyint NOT NULL,
  `log_action` tinyint NOT NULL,
  `log_time` tinyint NOT NULL,
  `log_cmt` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `acc_trustedips` (
  `trustedips_ipaddr` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;
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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `applicationlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `message` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `stack` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `request` varchar(1024) DEFAULT NULL,
  `request_ts` decimal(38,12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Logging from the application, not user actions. Used for debugging.';
/*!40101 SET character_set_client = @saved_cs_client */;
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
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `closes` (
  `closes` tinyint NOT NULL,
  `mail_desc` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;
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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailtemplate` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Table key',
  `name` varchar(255) NOT NULL COMMENT 'Name of the Email template',
  `text` blob NOT NULL COMMENT 'Text of the Email template',
  `jsquestion` longtext NOT NULL COMMENT 'Question in Javascript popup presented to the user when they attempt to use this template',
  `oncreated` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Deprecated - see defaultaction',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 if the template should be an available option to users. Default 1',
  `preloadonly` tinyint(1) NOT NULL DEFAULT '0',
  `defaultaction` varchar(45) DEFAULT NULL COMMENT 'The default action to take when this template is used for custom closes',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='Email templates';
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `emailtemplate` VALUES (1,'Created!','Many thanks for your interest in joining Wikipedia. I\'ve gone ahead and created the account for you. You will receive a separate automated e-mail from wiki@wikimedia.org with your login credentials. You can use these to log in for the first time, when you will be prompted to create a new password.\r\n\r\nWhen you have successfully logged in, you may find the &quot;getting started&quot; section of our help pages useful (http://en.wikipedia.org/wiki/Help:Contents/Getting_started). Of particular interest may be the introduction to Wikipedia (http://en.wikipedia.org/wiki/Wikipedia:Introduction) which has some information to help you get up to speed with the way things work on the encyclopedia.\r\n\r\nOne useful hint: when you have logged in for the first time and created your own password, go to your preferences (the link for them is right at the top of the screen), and ensure your email address is set where indicated. Should you forget your password, then this will allow you to have a new one sent to you!\r\n\r\nI wish you all the best and hope you enjoy your time on Wikipedia.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','Are you sure that you want to create? Please ensure you have completed all the checks.',1,1,0,'created'),(2,'Too Similar','Many thanks for your interest in joining Wikipedia. Unfortunately the username that you have requested is too similar to an active account, which may make it difficult for other contributors to distinguish you from the other user.\r\n\r\nPlease take a look at our username policy (http://en.wikipedia.org/wiki/Wikipedia:Username_policy) and choose a different username. You may be able to create the account with the new name you have chosen yourself at http://en.wikipedia.org/w/index.php?title=Special:Userlogin&amp;type=signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia.\r\n\r\nIf you are still unable to create the name yourself, we will gladly process your new request here, and I look forward to hearing from you again with your new choice of username.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','This action will send an email to the user. Are you sure that there is a conflict?',0,1,0,'not created'),(3,'Taken','Many thanks for your interest in joining Wikipedia.  Unfortunately the username that you have requested is already taken.  Please choose another username (unless you had created the account yourself after requesting it).  After you have chosen another username, you may be able to create the account with the new name you have chosen yourself at http://en.wikipedia.org/w/index.php?title=Special:Userlogin&amp;type=signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia. If you are still unable to create the account yourself, we will gladly process your new request here, so feel free to submit another request.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','his action will send an email to the user. Have you checked that the account is already taken?',0,1,0,'not created'),(4,'UPolicy','Many thanks for your interest in joining Wikipedia. Unfortunately the username that you have requested does not comply with our username policy, and so I am unable to create this account for you.\r\n\r\nPlease take a look at our username policy (http://en.wikipedia.org/wiki/Wikipedia:Username_policy) and choose a different username. You may be able to create the account with the new name you have chosen yourself at http://en.wikipedia.org/w/index.php?title=Special:Userlogin&amp;type=signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia.\r\n\r\nIf you are still unable to create the name yourself, we will gladly process your new request here, and I look forward to hearing from you again with your new choice of username.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','This action will send an email to the user. Are you sure that this request is a violation? Please read [[WP:UPOL]] completely before deciding.',0,1,0,'not created'),(5,'Invalid','Many thanks for your interest in joining Wikipedia. Unfortunately we are unable to process your request due to technical restrictions on usernames.\r\n\r\nPlease bear in mind that it is not possible to create usernames containing any of the characters # / | [ ] { } < > @ % : , consisting only of numbers, or ending with an underscore ( _ ) or a space, that have the same character repeating more than 10 times in a row, or are longer than 40 characters.\r\n\r\nPlease choose a username which is within these restrictions and then you can create an account by visiting http://en.wikipedia.org/wiki/Special:UserLogin/signup\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','This action will send an email to the user. Are you sure that the requested username is invalid?',0,1,0,'not created'),(6,'SUL Taken','Many thanks for your interest in joining Wikipedia.  Unfortunately the username that you have requested is already a part of a single user login (SUL) account, meaning that someone else has reserved that name through another one of our websites.\r\n\r\nPlease choose another username (unless you had created the account yourself after requesting it), or look at http://meta.wikimedia.org/wiki/Help:Unified_login for more information on SUL.  After you have chosen another username, you may be able to create the account yourself at http://en.wikipedia.org/w/index.php?title=Special:Userlogin&amp;type=signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia. If you are still unable to create the account yourself, we will gladly process your new request here, so feel free to submit another request.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','This action will send an email to the user. Have you checked that the account is already taken?',0,1,0,'not created'),(7,'Password Reset','Thank you for your interest in joining the English Wikipedia. At this time, we have not created your account as there are similar usernames that already exist on Wikipedia.\r\n\r\nWe have sent password reset emails to them in case you may have forgotten your password. If you do not receive any emails with a temporary password reset, it is possible that you do not own these accounts.\r\n\r\nIf that is so, please email us back and let us know.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','This action will send an email to the user. Are you sure that the requester owned the original reset account?',0,1,0,'not created'),(8,'Open Proxy','Hello and thank you for your interest in joining Wikipedia.\r\n\r\nLooking at our logs, it appears the IP address you\'re requesting from belongs to a proxy or web hosting service. Open or anonymising proxies, including web hosts, are blocked from editing Wikipedia. While this may affect legitimate users, they are not the intended targets. No restrictions are placed on reading Wikipedia through an open or anonymous proxy.\r\n\r\nAlthough Wikipedia encourages anyone in the world to contribute, open proxies are often used abusively. MediaWiki, the wiki software that powers Wikipedia, depends on IP addresses for administrator intervention against abuse, especially by anonymous users. Open proxies allow malicious users to rapidly change IP addresses, causing continuous disruption that cannot be stopped by administrators. Several such attacks have occurred on Wikimedia projects.\r\n\r\nUnfortunately, you won\'t be able to edit while using this open proxy.\r\n\r\nIf you use a different IP address you may be able to create the account yourself at http://en.wikipedia.org/w/index.php?title=Special:Userlogin&type=signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia. If you are still unable to create the account yourself, we will gladly process your new request here, and I look forward to hearing from you again.','Are you certain that this IP address is an open proxy? Being blocked as an open proxy is not sufficient evidence. If in doubt, defer to proxy check.',0,1,0,'not created'),(9,'Notable Person','Hello and thank you for your interest in joining Wikipedia.\r\n     \r\nUnfortunately, we cannot fulfill your account request at this time. To help protect the identities of famous people, requests for usernames matching the real name of a living, notable person must first be verified before they are acted upon. You are welcome to use your real name, but you will need to prove you are who you say you are. You can do this by sending an e-mail to info-en@wikimedia.org and talking with our volunteers there. To ensure that this request is handled correctly, please forward this email to them, as well as provide some form of official documentation proving your identity. This will allow the volunteers there to effectively communicate with us regarding this request. Be aware that e-mails are handled by a volunteer response team, and an immediate reply is not always possible. If the request sits for over a week, feel free to reply to this message and let us know it is taking a while, and we will try and speed up the process for you.\r\n     \r\nIf this is not your name, you will need to edit under a different username, you may be able to create the account with the new username you have chosen yourself at https://en.wikipedia.org/wiki/Special:UserLogin/signup. If so, I wish you all the best and hope you enjoy your time on Wikipedia. If you are still unable to create the username yourself, we will gladly process your new request here.','Are you certain that the username resembles a notable person that is alive?',0,1,0,'not created'),(10,'Shared Username','Many thanks for your interest in joining the English Wikipedia. Unfortunately the username that you requested does not comply with our username policy. I am unable to create the account your requested because it implies that more than one person may use the account. Wikipedia\'s policy is that a user account should be used only by one person and that one person should use only one account in most cases. \r\n\r\nPlease read Wikipedia\'s username policy at http://en.wikipedia.org/wiki/Wikipedia:UPOL#Usernames_implying_shared_use and http://en.wikipedia.org/wiki/Wikipedia:UPOL#Promotional_names. You will have to choose a different username. You may be able to create the account with a new name you have chosen yourself by going to http://en.wikipedia.org/w/index.php?title=Special:Userlogin&amp;type=signup. You can see what usernames are available at http://en.wikipedia.org/wiki/Special:ListUsers. Enter the username you chose at \"Display users starting at:\" and click Go. If you are able to create your account, great and I wish you all the best and hope you enjoy your stay at Wikipedia. If you are unable to create the account yourself, we will gladly process your request here. Make a new request at http://en.wikipedia.org/wiki/Wikipedia:ACC. \r\n\r\nPlease also familiarize yourself with some other Wikipedia policies before you begin contributing to Wikipedia. See http://en.wikipedia.org/wiki/Wikipedia:PROMOTION and http://en.wikipedia.org/wiki/Wikipedia:COI for more information. You may find the advice in http://en.wikipedia.org/wiki/Wikipedia:COISIMPLE helpful.','Are you sure the username implies shared use?',0,1,0,'not created'),(11,'Long response time','Many thanks for your interest in joining Wikipedia. I\'ve gone ahead and created the account for you. You will receive a separate automated e-mail from wiki@wikimedia.org with your login credentials.  Use them to log in for the first time.  You will then be prompted to create a new password.  After you create your own password, I suggest that you go to your preferences (the link for them is right at the top of the screen) and ensure your email address is set where indicated.  If you forget your password, this will allow you to have a new one sent to you.\r\n\r\nPlease accept my apology for how long it took to approve your account.  A combination of software updates, too few volunteers, some extra checks that were needed and an unusually large number of requests contributed to the long delay.\r\n\r\nI wish you all the best and hope you enjoy your time on Wikipedia.','Did this request take an unusually long time to create due to requring CheckUser attention, a long backlog, etc.?',1,1,0,'created'),(12,'COI risk','Many thanks for your interest in joining Wikipedia. I\'ve gone ahead and created the account for you. You will receive a separate automated e-mail from wiki@wikimedia.org with your login credentials. You can use these to log in for the first time, when you will be prompted to create a new password.\r\n\r\nWhen you have successfully logged in, you may find the \"getting started\" section of our help pages useful <https://en.wikipedia.org/wiki/Help:Contents/Getting_started>. Of particular interest may be the introduction to Wikipedia <https://en.wikipedia.org/wiki/Wikipedia:Introduction> which has some information to help you get up to speed with the way things work on the encyclopedia.\r\n\r\nSince it appears that you may have ties to a business or have ties to/are a famous person, I would like to take a moment to remind you that Wikipedia is not to be used for promotional purposes.  Before editing, please read Wikipedia\'s \"Best Practices for Editors With Close Associations\" at <https://en.wikipedia.org/wiki/Wikipedia:BPCA>, the \"Plain and Simple Conflict of Interest Guide\" at <https://en.wikipedia.org/wiki/Wikipedia:PSCOI>, and Wikipedia\'s policies on promotion at <https://en.wikipedia.org/wiki/Wikipedia:PROMOTION>.  Understanding these policies will help prevent misunderstandings between you and Wikipedia\'s administrators.\r\n\r\nOne useful hint: when you have logged in for the first time and created your own password, go to your preferences (the link for them is right at the top of the screen), and ensure your email address is set where indicated. Should you forget your password, then this will allow you to have a new one sent to you!\r\n\r\nI wish you all the best and hope you enjoy your time on Wikipedia.\r\n\r\n*If you did not make this request, please ignore this email. If you wish to report this, please send an email to accounts-enwiki-l@lists.wikimedia.org with a copy of the original email.*','This email is just like the standard Created email, but including links to WP:BPCA, WP:PSCOI, and WP:PROMOTION.  It is intended to be used for accounts that were created but you suspect that the requester has ties to a company or other entity and may present a COI risk (for example, user used a corporate email address in their account request).  Is this the template you wish to use?',1,1,0,'created');
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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `id` (
  `enwikiname` varchar(50) NOT NULL,
  PRIMARY KEY (`enwikiname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
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
INSERT INTO `interfacemessage` VALUES (10,'<span class =\"declined\">I\'m sorry, but the username you selected is already taken. Please try another.\r\n\r\nPlease note that Wikipedia automatically capitalizes the first letter of any user name, therefore [[User:example]] would become [[User:Example]].</span>',0,'Declined - Taken [deprecated]','Interface'),(11,'<span class =\"declined\">The username you chose is invalid: it consists entirely of numbers. Please retry with a valid username.</span>',0,'Declined - Numbers Only [deprecated]','Interface'),(12,'<span class =\"declined\">The username you chose is invalid: Your username may not be an e-mail address, which it appears to be.</span>',0,'Declined - Username Is Email [deprecated]','Interface'),(13,'<span class =\"declined\">The username you chose is invalid: Your username may not contain the following characters # / | [ ] { } &lt; &gt; @ % : [two consecutive blank spaces]</span>',2,'Declined - Invalid Chars [deprecated]','Interface'),(14,'<span class =\"declined\">Invalid E-mail address supplied. Please check you entered it correctly.</span>',0,'Declined - Invalid Email [deprecated]','Interface'),(16,'The system has <strong>not</strong> submitted this request.',0,'Decline - Final [deprecated]','Interface'),(17,'<font color=\"red\" size=\"4\">There is already an open request for this username. Please choose another.</font>',0,'Decline - Dup Request (Username) [deprecated]','Interface'),(18,'<font color=\"red\" size=\"4\">I\'m sorry, but you have already put in a request. Please do not submit multiple requests.</font>',0,'Decline - Dup Request (Email) [deprecated]','Interface'),(19,'I\'m sorry, but you are currently banned from requesting accounts using this tool. However, you can still send an email to <a href=\"mailto:accounts-enwiki-l@lists.wikimedia.org\">accounts-enwiki-l@lists.wikimedia.org</a> to request an account. \r\nThe ban on using this tool was given for the following reason: ',0,'Banned [deprecated]','Interface'),(20,'<script type=\"text/javascript\" language=\"JavaScript\">\r\n<!--\r\nvar cookieName = \"dismissSiteNotice=\";\r\nvar cookiePos = document.cookie.indexOf(cookieName);\r\nvar siteNoticeID = \"%SITENOTICECOUNT%\";\r\nvar siteNoticeValue=\"%SITENOTICETEXT%\";\r\nvar cookieValue = \"\";\r\nvar msgClose = \"Dismiss\";\r\nvar msgShow=\"Show sitenotice\";\r\nvar hideTitle=\"Hide sitenotice for one week\"\r\nvar showTitle=\"Show sitenotice\"\r\nfunction updateCookie(){\r\nif (cookiePos > -1) {\r\n	cookiePos = cookiePos + cookieName.length;\r\n	var endPos = document.cookie.indexOf(\";\", cookiePos);\r\n	if (endPos > -1) {\r\n		cookieValue = document.cookie.substring(cookiePos, endPos);\r\n	} else {\r\n		cookieValue = document.cookie.substring(cookiePos);\r\n	}\r\n}\r\n\r\n}\r\nupdateCookie();\r\n	function dismissNotice() {\r\n		var date = new Date();\r\n		date.setTime(date.getTime() + 7*24*60*60*1000);\r\n		var element = document.getElementById(\'innerNotice\');\r\n		var dismissButton=document.getElementById(\'dismiss\');\r\n		if(dismissButton.childNodes[0].innerHTML==\"[\"+msgClose+\"]\"){\r\n			element.style.display = \"none\";\r\n			dismissButton.childNodes[0].innerHTML=\"[\"+msgShow+\"]\";\r\n			document.getElementById(\'sitenotice\').style.paddingBottom=\"20px\";\r\n       			 document.getElementById(\'dismiss\').title=showTitle\r\n			document.cookie = cookieName + siteNoticeID + \"; expires=\"+date.toGMTString() + \"; path=/\";\r\n		}else{\r\n			element.style.display = \"block\";\r\n			dismissButton.childNodes[0].innerHTML=\"[\"+msgClose+\"]\";\r\n     			document.getElementById(\'dismiss\').title=hideTitle\r\n			document.getElementById(\'sitenotice\').style.paddingBottom=\"8px\";\r\n			document.cookie = cookieName + \"0\" + \"; expires=\"+date.toGMTString() + \"; path=/\";\r\n		}\r\nupdateCookie();\r\n	}\r\ndocument.writeln(\'	<div id=\"sitenotice\">\');\r\ndocument.writeln(\'<span title=\"\'+showTitle+\'\" id=\"dismiss\"><a href=\"javascript:dismissNotice();\">[\'+msgShow+\']</a></span>\');\r\ndocument.writeln(\'	<div id=\"innerNotice\" style=\"display:none\">\');\r\ndocument.writeln(siteNoticeValue);\r\ndocument.writeln(\'</div>\');\r\nif (cookieValue != siteNoticeID&&cookieValue != \"0\") {\r\n	document.getElementById(\'dismiss\').childNodes[0].innerHTML=\"[\"+msgClose+\"]\";\r\n        document.getElementById(\'dismiss\').title=hideTitle\r\n	document.getElementById(\'innerNotice\').style.display=\"block\";\r\n}\r\ndocument.writeln(\'	</div>\');\r\ndocument.getElementById(\'sitenotice\').style.paddingBottom=\"20px\";\r\nif (cookieValue != siteNoticeID&&cookieValue != \"0\") {\r\n	document.getElementById(\'sitenotice\').style.paddingBottom=\"8px\";\r\n}\r\n-->\r\n</script>',9,'Sitenotice code [deprecated]','Internal'),(27,'<span class =\"declined\">The email addresses you entered do not match. Please try again.</span>',0,'Declined - Emails do not match [deprecated]','interface'),(28,'<span class =\"declined\">I\'m sorry, but the username you selected is already a part of a SUL account.  Please try another username, or look at <a href=\"http://meta.wikimedia.org/wiki/Help:Unified_login\">this page</a> for more information on SUL. <br />Please note that Wikipedia automatically capitalizes the first letter of any user name, therefore [[User:example]] would become [[User:Example]].</span>',0,'Declined - SUL Taken [deprecated]','Interface'),(31,'<ul>\r\n<li><strong>Please ensure you\'ve read through the <a href=\"https://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide\">guide</a> and <a href=\"https://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Procedures\">procedures</a> pages <i>before</i> you begin dealing with requests</strong><em>; keep in mind that it may have been updated since you last looked.</em> \"I didn\'t know\" is not an excuse and <u>will not</u> prevent you being suspended.</li>\r\n<li>Please write emails and custom closes in plain, simple English that newcomers to Wikipedia can understand.</li>\r\n<li><span class=\"text-error\">Releasing personally identifying information or placing such information in the comments is not allowed and will get you suspended.</span> Please see <a href=\"https://accounts-dev.wmflabs.org/other/identinfoemail.html\">this email</a> for details.</li>\r\n<li>FastLizard4 is the ACC/OTRS liaison.  If you have any queries, concerns, or comments regarding an ACC- and OTRS-related issue (for example, you deferred an account request to OTRS for identity verification and you\'d like to check on its status), please contact him directly, either by IRC private message or by email to <a href=\"mailto:fastlizard4@gmail.com\">fastlizard4@gmail.com</a>.<span style=\"font-size: smaller;\">  GPG users may encrypt email communications with key <tt>0x 221A 627D D76E 2616</tt>.</span></li>\r\n<li><strong>A new account created email, \"COI risk\", has been created.</strong>  It is like the standard \"account created\" email, but includes links to WP:PROMOTION, WP:BPCA, and WP:COISIMPLE.  It is available both directly as a close email, as well as a preload template for custom closes.  (You may wish to read the email template before using it as a direct close email.)</li>\r\n</ul>',531,'Sitenotice text','Internal');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objectid` int(11) NOT NULL,
  `objecttype` varchar(45) NOT NULL,
  `user` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` datetime NOT NULL,
  `comment` blob,
  PRIMARY KEY (`id`),
  KEY `log_idx_action` (`action`),
  KEY `log_idx_objectid` (`objectid`),
  KEY `log_idx_user` (`user`),
  KEY `log_idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(512) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `name` varchar(512) NOT NULL,
  `comment` longtext NOT NULL,
  `status` varchar(255) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `checksum` varchar(256) NOT NULL,
  `emailsent` varchar(10) NOT NULL,
  `emailconfirm` varchar(255) NOT NULL,
  `reserved` int(11) NOT NULL DEFAULT '0' COMMENT 'User ID of user who has "reserved" this request',
  `useragent` blob NOT NULL COMMENT 'Useragent of the requesting web browser',
  `forwardedip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `acc_pend_status_mailconf` (`status`,`emailconfirm`),
  KEY `pend_ip_status` (`ip`,`emailconfirm`),
  KEY `pend_email_status` (`email`(255),`emailconfirm`),
  KEY `ft_useragent` (`useragent`(512)),
  KEY `ip` (`ip`),
  KEY `mailconfirm` (`emailconfirm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schemaversion` (
  `version` int(11) NOT NULL DEFAULT '10',
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Current schema version for use by update scripts';
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `schemaversion` VALUES (11,'2016-01-02 03:15:31');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `user` VALUES (1,'Admin','admin@localhost',':1:salt:c72deb17cc624f7bbe4ad632887b0202','Admin','MediaWiki User','','0000-00-00 00:00:00',0,0,1,0,0,0,'Admin',NULL,NULL,NULL,NULL,NULL);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `welcometemplate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usercode` text NOT NULL,
  `botcode` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `welcometemplate` VALUES (1,'{{welcome|user}} ~~~~','{{subst:Welcome|$username}}$signature'),(2,'{{welcomeg|user}} ~~~~','== Welcome! ==\n\n{{subst:Welcomeg|$username|sig=$signature}}'),(3,'{{welcome-personal|user}} ~~~~','{{subst:Welcome-personal|$username||$signature}}'),(5,'{{WelcomeMenu|sig=~~~~}}','== Welcome! ==\n\n{{subst:WelcomeMenu|sig=$signature}}'),(6,'{{WelcomeIcon}} ~~~~','== Welcome! ==\n\n{{subst:WelcomeIcon}} $signature'),(7,'{{WelcomeShout|user}} ~~~~','{{subst:WelcomeShout|$username}} $signature'),(8,'{{Welcomeshort|user}} ~~~~','{{subst:Welcomeshort|$username}} $signature'),(9,'{{Welcomesmall|user}} ~~~~','{{subst:Welcomesmall|$username}} $signature'),(13,'{{w-screen|sig=~~~~}}','== Welcome! ==\n\n{{subst:w-screen|sig=$signature}}'),(28,'{{Welcome0|1=user|sig=~~~~}}','{{subst:Welcome0|1=$username|sig=$signature}}');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xfftrustcache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_xfftrustcache_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50001 DROP TABLE IF EXISTS `acc_emails`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*DISABLED: 50013 DEFINER=`jenkins_build`@`%.lon.stwalkerster.net` SQL SECURITY DEFINER */
/*!50001 VIEW `acc_emails` AS select `interfacemessage`.`id` AS `mail_id`,`interfacemessage`.`content` AS `mail_content`,`interfacemessage`.`updatecounter` AS `mail_count`,`interfacemessage`.`description` AS `mail_desc`,`interfacemessage`.`type` AS `mail_type` from `interfacemessage` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP TABLE IF EXISTS `acc_log`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*DISABLED: 50013 DEFINER=`jenkins_build`@`%.lon.stwalkerster.net` SQL SECURITY DEFINER */
/*!50001 VIEW `acc_log` AS select `l`.`id` AS `log_id`,`l`.`objectid` AS `log_pend`,(case when (`l`.`action` = 'Email Confirmed') then `r`.`name` else `u`.`username` end) AS `log_user`,`l`.`action` AS `log_action`,`l`.`timestamp` AS `log_time`,`l`.`comment` AS `log_cmt` from ((`log` `l` left join `user` `u` on((`l`.`user` = `u`.`id`))) left join `request` `r` on((`l`.`objectid` = `r`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP TABLE IF EXISTS `acc_trustedips`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*DISABLED: 50013 DEFINER=`jenkins_build`@`%.lon.stwalkerster.net` SQL SECURITY DEFINER */
/*!50001 VIEW `acc_trustedips` AS select `xfftrustcache`.`id` AS `trustedips_ipaddr` from `xfftrustcache` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP TABLE IF EXISTS `closes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*DISABLED: 50013 DEFINER=`jenkins_build`@`%.lon.stwalkerster.net` SQL SECURITY DEFINER */
/*!50001 VIEW `closes` AS select concat('Closed ',cast(`emailtemplate`.`id` as char charset utf8)) AS `closes`,`emailtemplate`.`name` AS `mail_desc` from `emailtemplate` union select 'Closed 0' AS `Closed 0`,'Dropped' AS `Dropped` union select 'Closed custom' AS `Closed custom`,'Closed custom' AS `My_exp_Closed custom` union select 'Closed custom-n' AS `Closed custom-n`,'Closed custom - Not created' AS `Closed custom - Not created` union select 'Closed custom-y' AS `Closed custom-y`,'Closed custom - Created' AS `Closed custom - Created` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
