DROP DATABASE IF EXISTS p_acc_notifications;
CREATE DATABASE p_acc_notifications;

CREATE TABLE p_acc_notifications.`notification_type` (
  `nt_id` INT NOT NULL AUTO_INCREMENT ,
  `nt_name` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`nt_id`) ,
  UNIQUE INDEX `u_nt_name` (`nt_name` ASC) );
  
INSERT INTO p_acc_notifications.`notification_type` (`nt_name`) VALUES ('ACCBot');

CREATE TABLE p_acc_notifications.`notification` (
  `notif_id` INT NOT NULL AUTO_INCREMENT ,
  `notif_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `notif_type` INT NOT NULL DEFAULT 1 ,
  `notif_text` VARCHAR(1024) NOT NULL ,
  PRIMARY KEY (`notif_id`) )
ENGINE = MEMORY;

DROP procedure IF EXISTS `p_acc_notifications`.`bot_notify`;

DELIMITER $$
CREATE PROCEDURE `p_acc_notifications`.`bot_notify` (in notif_text varchar(1024) )
BEGIN
INSERT INTO `p_acc_notifications`.`notification` (`notif_log`) VALUES (notif_text);
END$$
DELIMITER ;
