ALTER TABLE `acc_user` ALTER COLUMN `user_welcome_sig` SET DEFAULT '';
ALTER TABLE `acc_user` ALTER COLUMN `user_welcome_templateid` SET DEFAULT '0';
ALTER TABLE `acc_user` DROP COLUMN `user_welcome` SET DEFAULT '1';