ALTER TABLE `acc_ban` DROP INDEX `ban_target`; 

ALTER TABLE `acc_ban` ADD COLUMN `ban_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `ban_duration`;