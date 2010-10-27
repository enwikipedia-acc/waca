DROP TABLE IF EXISTS `acc_trustedips`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `acc_trustedips` (
  `trustedips_ipaddr` varchar(15) NOT NULL,
  PRIMARY KEY (`trustedips_ipaddr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;