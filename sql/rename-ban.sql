DROP VIEW IF EXISTS ban;

ALTER TABLE `acc_ban` 
CHANGE COLUMN `ban_id` `id` INT(11) NOT NULL ,
CHANGE COLUMN `ban_type` `type` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `ban_target` `target` VARCHAR(700) NOT NULL ,
CHANGE COLUMN `ban_user` `user` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `ban_reason` `reason` VARCHAR(4096) NOT NULL ,
CHANGE COLUMN `ban_date` `date` VARCHAR(1024) NOT NULL ,
CHANGE COLUMN `ban_duration` `duration` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `ban_active` `active` TINYINT(1) NOT NULL DEFAULT '1' , RENAME TO `ban` ;
