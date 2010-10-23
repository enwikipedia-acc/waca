ALTER TABLE `acc_user` ALTER COLUMN `user_welcome_templateid` SET DEFAULT '0';
ALTER TABLE `acc_user` ALTER COLUMN `user_welcome_sig` SET DEFAULT '';
UPDATE `acc_user` SET `user_welcome_templateid` = '0' WHERE `user_welcome` < '1';
ALTER TABLE `acc_user` DROP COLUMN `user_welcome`;
