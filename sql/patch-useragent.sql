ALTER TABLE `acc_pend` ADD `pend_useragent` BLOB NOT NULL DEFAULT '-No Useragent stored-' COMMENT 'Useragent of the requesting web browser';

ALTER TABLE `acc_user` ADD `user_checkuser` INT(1) NOT NULL DEFAULT 0;
