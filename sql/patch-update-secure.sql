ALTER TABLE `acc_user` 
CHANGE COLUMN `user_secure` `user_secure` INT(11) NOT NULL DEFAULT '1' ;

UPDATE `acc_user` SET user_secure = 1;