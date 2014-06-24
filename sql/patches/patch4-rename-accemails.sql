drop view if exists interfacemessage;

ALTER TABLE `acc_emails` 
CHANGE COLUMN `mail_id` `id` INT(11) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `mail_text` `content` BLOB NOT NULL ,
CHANGE COLUMN `mail_count` `updatecounter` INT(11) NOT NULL ,
CHANGE COLUMN `mail_desc` `description` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `mail_type` `type` VARCHAR(255) NOT NULL , 
RENAME TO  `interfacemessage` ;
