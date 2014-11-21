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
-- Dumping data for table `interfacemessage`
--

INSERT INTO `interfacemessage` VALUES (10,'<span class =\"declined\">I\'m sorry, but the username you selected is already taken. Please try another.\r\n\r\nPlease note that Wikipedia automatically capitalizes the first letter of any user name, therefore [[User:example]] would become [[User:Example]].</span>',0,'Declined - Taken','Interface');
INSERT INTO `interfacemessage` VALUES (11,'<span class =\"declined\">The username you chose is invalid: it consists entirely of numbers. Please retry with a valid username.</span>',0,'Declined - Numbers Only','Interface');
INSERT INTO `interfacemessage` VALUES (12,'<span class =\"declined\">The username you chose is invalid: Your username may not be an e-mail address, which it appears to be.</span>',0,'Declined - Username Is Email','Interface');
INSERT INTO `interfacemessage` VALUES (13,'<span class =\"declined\">The username you chose is invalid: Your username may not contain the following characters # / | [ ] { } &lt; &gt; @ % : [two consecutive blank spaces]</span>',2,'Declined - Invalid Chars','Interface');
INSERT INTO `interfacemessage` VALUES (14,'<span class =\"declined\">Invalid E-mail address supplied. Please check you entered it correctly.</span>',0,'Declined - Invalid Email','Interface');
INSERT INTO `interfacemessage` VALUES (16,'The system has <strong>not</strong> submitted this request.',0,'Decline - Final','Interface');
INSERT INTO `interfacemessage` VALUES (17,'<font color=\"red\" size=\"4\">There is already an open request for this username. Please choose another.</font>',0,'Decline - Dup Request (Username)','Interface');
INSERT INTO `interfacemessage` VALUES (18,'<font color=\"red\" size=\"4\">I\'m sorry, but you have already put in a request. Please do not submit multiple requests.</font>',0,'Decline - Dup Request (Email)','Interface');
INSERT INTO `interfacemessage` VALUES (19,'I\'m sorry, but you are currently banned from requesting accounts using this tool. However, you can still send an email to <a href=\"mailto:accounts-enwiki-l@lists.wikimedia.org\">accounts-enwiki-l@lists.wikimedia.org</a> to request an account. \r\nThe ban on using this tool was given for the following reason: ',0,'Banned','Interface');
INSERT INTO `interfacemessage` VALUES (20,'<script type=\"text/javascript\" language=\"JavaScript\">\r\n<!--\r\nvar cookieName = \"dismissSiteNotice=\";\r\nvar cookiePos = document.cookie.indexOf(cookieName);\r\nvar siteNoticeID = \"%SITENOTICECOUNT%\";\r\nvar siteNoticeValue=\"%SITENOTICETEXT%\";\r\nvar cookieValue = \"\";\r\nvar msgClose = \"Dismiss\";\r\nvar msgShow=\"Show sitenotice\";\r\nvar hideTitle=\"Hide sitenotice for one week\"\r\nvar showTitle=\"Show sitenotice\"\r\nfunction updateCookie(){\r\nif (cookiePos > -1) {\r\n	cookiePos = cookiePos + cookieName.length;\r\n	var endPos = document.cookie.indexOf(\";\", cookiePos);\r\n	if (endPos > -1) {\r\n		cookieValue = document.cookie.substring(cookiePos, endPos);\r\n	} else {\r\n		cookieValue = document.cookie.substring(cookiePos);\r\n	}\r\n}\r\n\r\n}\r\nupdateCookie();\r\n	function dismissNotice() {\r\n		var date = new Date();\r\n		date.setTime(date.getTime() + 7*24*60*60*1000);\r\n		var element = document.getElementById(\'innerNotice\');\r\n		var dismissButton=document.getElementById(\'dismiss\');\r\n		if(dismissButton.childNodes[0].innerHTML==\"[\"+msgClose+\"]\"){\r\n			element.style.display = \"none\";\r\n			dismissButton.childNodes[0].innerHTML=\"[\"+msgShow+\"]\";\r\n			document.getElementById(\'sitenotice\').style.paddingBottom=\"20px\";\r\n       			 document.getElementById(\'dismiss\').title=showTitle\r\n			document.cookie = cookieName + siteNoticeID + \"; expires=\"+date.toGMTString() + \"; path=/\";\r\n		}else{\r\n			element.style.display = \"block\";\r\n			dismissButton.childNodes[0].innerHTML=\"[\"+msgClose+\"]\";\r\n     			document.getElementById(\'dismiss\').title=hideTitle\r\n			document.getElementById(\'sitenotice\').style.paddingBottom=\"8px\";\r\n			document.cookie = cookieName + \"0\" + \"; expires=\"+date.toGMTString() + \"; path=/\";\r\n		}\r\nupdateCookie();\r\n	}\r\ndocument.writeln(\'	<div id=\"sitenotice\">\');\r\ndocument.writeln(\'<span title=\"\'+showTitle+\'\" id=\"dismiss\"><a href=\"javascript:dismissNotice();\">[\'+msgShow+\']</a></span>\');\r\ndocument.writeln(\'	<div id=\"innerNotice\" style=\"display:none\">\');\r\ndocument.writeln(siteNoticeValue);\r\ndocument.writeln(\'</div>\');\r\nif (cookieValue != siteNoticeID&&cookieValue != \"0\") {\r\n	document.getElementById(\'dismiss\').childNodes[0].innerHTML=\"[\"+msgClose+\"]\";\r\n        document.getElementById(\'dismiss\').title=hideTitle\r\n	document.getElementById(\'innerNotice\').style.display=\"block\";\r\n}\r\ndocument.writeln(\'	</div>\');\r\ndocument.getElementById(\'sitenotice\').style.paddingBottom=\"20px\";\r\nif (cookieValue != siteNoticeID&&cookieValue != \"0\") {\r\n	document.getElementById(\'sitenotice\').style.paddingBottom=\"8px\";\r\n}\r\n-->\r\n</script>',9,'Sitenotice code','Internal');
INSERT INTO `interfacemessage` VALUES (27,'<span class =\"declined\">The email addresses you entered do not match. Please try again.</span>',0,'Declined - Emails do not match','interface');
INSERT INTO `interfacemessage` VALUES (28,'<span class =\"declined\">I\'m sorry, but the username you selected is already a part of a SUL account.  Please try another username, or look at <a href=\"http://meta.wikimedia.org/wiki/Help:Unified_login\">this page</a> for more information on SUL. <br />Please note that Wikipedia automatically capitalizes the first letter of any user name, therefore [[User:example]] would become [[User:Example]].</span>',0,'Declined - SUL Taken','Interface');
INSERT INTO `interfacemessage` VALUES (31,'<ul>\r\n<li><strong>Please ensure you\'ve read through the <a href=\"https://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide\">guide</a> and <a href=\"https://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Procedures\">procedures</a> pages <i>before</i> you begin dealing with requests</strong><em>; keep in mind that it may have been updated since you last looked.</em> \"I didn\'t know\" is not an excuse and <u>will not</u> prevent you being suspended.</li>\r\n<li>Please write emails and custom closes in plain, simple English that newcomers to Wikipedia can understand.</li>\r\n<li><span class=\"text-error\">Releasing personally identifying information or placing such information in the comments is not allowed and will get you suspended.</span> Please see <a href=\"https://accounts-dev.wmflabs.org/other/identinfoemail.html\">this email</a> for details.</li>\r\n<li>FastLizard4 is the ACC/OTRS liaison.  If you have any queries, concerns, or comments regarding an ACC- and OTRS-related issue (for example, you deferred an account request to OTRS for identity verification and you\'d like to check on its status), please contact him directly, either by IRC private message or by email to <a href=\"mailto:fastlizard4@gmail.com\">fastlizard4@gmail.com</a>.<span style=\"font-size: smaller;\">  GPG users may encrypt email communications with key <tt>0x 221A 627D D76E 2616</tt>.</span></li>\r\n<li><strong>A new account created email, \"COI risk\", has been created.</strong>  It is like the standard \"account created\" email, but includes links to WP:PROMOTION, WP:BPCA, and WP:COISIMPLE.  It is available both directly as a close email, as well as a preload template for custom closes.  (You may wish to read the email template before using it as a direct close email.)</li>\r\n</ul>',531,'Sitenotice text','Internal');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-21 19:28:47
