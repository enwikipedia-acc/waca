ALTER TABLE `acc_user` 
ADD COLUMN `oauthidentitycache` BLOB NULL DEFAULT NULL AFTER `oauthaccesssecret`,
DROP INDEX `user_onwikiname_UNIQUE` ;

