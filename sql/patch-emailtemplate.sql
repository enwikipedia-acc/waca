DROP TABLE IF EXISTS `acc_newmail`;
DROP TABLE IF EXISTS `emailtemplate`;
CREATE TABLE IF NOT EXISTS `emailtemplate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `text` blob NOT NULL,
  `jsquestion` mediumtext NOT NULL,
  `oncreated` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Email templates';
