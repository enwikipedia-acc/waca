 CREATE TABLE `acc_emails` (
`mail_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`mail_text` BLOB NOT NULL ,
`mail_count` INT( 11 ) NOT NULL ,
`mail_desc` VARCHAR( 255 ) NOT NULL ,
`mail_type` VARCHAR( 255 ) NOT NULL
) ENGINE = InnoDB; 

 CREATE TABLE `acc_log` (
`log_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`log_pend` VARCHAR( 255 ) NOT NULL ,
`log_user` VARCHAR( 255 ) NOT NULL ,
`log_action` VARCHAR( 255 ) NOT NULL ,
`log_time` DATETIME NOT NULL ,
`log_cmt` BLOB NOT NULL
) ENGINE = InnoDB; 

 CREATE TABLE `acc_pend` (
`pend_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`pend_email` VARCHAR( 512 ) NOT NULL ,
`pend_ip` VARCHAR( 255 ) NOT NULL ,
`pend_name` VARCHAR( 512 ) NOT NULL ,
`pend_cmt` MEDIUMTEXT NOT NULL ,
`pend_status` VARCHAR( 255 ) NOT NULL,
`pend_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`pend_checksum` VARCHAR( 256 ) NOT NULL ,
`pend_emailsent` VARCHAR( 10 ) NOT NULL ,
`pend_mailconfirm` VARCHAR( 255 ) NOT NULL
) ENGINE = InnoDB; 

 CREATE TABLE `acc_user` (
`user_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`user_name` VARCHAR( 255 ) NOT NULL ,
`user_email` VARCHAR( 255 ) NOT NULL ,
`user_pass` VARCHAR( 255 ) NOT NULL ,
`user_level` VARCHAR( 255 ) NOT NULL ,
`user_onwikiname` VARCHAR( 255 ) NOT NULL ,
`user_welcome` INT( 11 ) NOT NULL DEFAULT 0 ,
`user_welcome_sig` VARCHAR( 4096 ) NOT NULL ,
`user_welcome_template` VARCHAR( 1024 ) NOT NULL ,
`user_lastactive` DATETIME NOT NULL ,
`user_forcelogout` INT( 3 ) NOT NULL
) ENGINE = InnoDB; 

 CREATE TABLE `acc_ban` (
`ban_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`ban_type` VARCHAR( 255 ) NOT NULL ,
`ban_target` VARCHAR( 700 ) NOT NULL UNIQUE ,
`ban_user` VARCHAR( 255 ) NOT NULL ,
`ban_reason` VARCHAR( 4096 ) NOT NULL ,
`ban_date` VARCHAR( 1024 ) NOT NULL ,
`ban_duration` VARCHAR( 255 ) NOT NULL 
) ENGINE = InnoDB;

 CREATE TABLE `acc_welcome` (
`welcome_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`welcome_uid` INT( 11 ) NOT NULL ,
`welcome_user` VARCHAR( 1024 ) NOT NULL ,
`welcome_sig` VARCHAR( 4096 ) NOT NULL ,
`welcome_status` VARCHAR( 96 ) NOT NULL ,
`welcome_pend` INT( 11 ) NOT NULL ,
`welcome_template` VARCHAR( 2048 ) NOT NULL 
) ENGINE = InnoDB;