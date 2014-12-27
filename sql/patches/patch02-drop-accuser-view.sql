DROP VIEW IF EXISTS acc_user;
DROP VIEW IF EXISTS acc_template;

DROP VIEW IF EXISTS `request`;
ALTER TABLE `acc_pend` 
CHANGE COLUMN `pend_id` `id` INT(11) NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `pend_email` `email` VARCHAR(512) NOT NULL ,
CHANGE COLUMN `pend_ip` `ip` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `pend_name` `name` VARCHAR(512) NOT NULL ,
CHANGE COLUMN `pend_cmt` `comment` LONGTEXT NOT NULL ,
CHANGE COLUMN `pend_status` `status` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `pend_date` `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
CHANGE COLUMN `pend_checksum` `checksum` VARCHAR(256) NOT NULL ,
CHANGE COLUMN `pend_emailsent` `emailsent` VARCHAR(10) NOT NULL ,
CHANGE COLUMN `pend_mailconfirm` `emailconfirm` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `pend_reserved` `reserved` INT(11) NOT NULL DEFAULT '0' COMMENT 'User ID of user who has \"reserved\" this request' ,
CHANGE COLUMN `pend_useragent` `useragent` BLOB NOT NULL COMMENT 'Useragent of the requesting web browser' ,
CHANGE COLUMN `pend_proxyip` `forwardedip` VARCHAR(255) NULL , 
RENAME TO `request` ;
