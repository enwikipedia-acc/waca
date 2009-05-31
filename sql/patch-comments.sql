CREATE TABLE IF NOT EXISTS `acc_cmt` (
  `cmt_id` int(11) NOT NULL AUTO_INCREMENT,
  `cmt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cmt_user` varchar(255) NOT NULL,
  `cmt_comment` mediumtext NOT NULL,
  `cmt_visability` varchar(255) NOT NULL,
  `pend_id` int(11) NOT NULL,
  PRIMARY KEY (`cmt_id`),
  UNIQUE KEY `cmt_id` (`cmt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
