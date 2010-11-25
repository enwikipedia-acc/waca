DROP TABLE IF EXISTS `acc_titleblacklist`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `acc_titleblacklist` (
  `titleblacklist_regex` varchar(128) NOT NULL,
  `titleblacklist_ipaddr` tinyint(1) NOT NULL
  PRIMARY KEY (`titleblacklist_regex`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;