DROP VIEW IF EXISTS user;

ALTER TABLE `acc_user` 
DROP COLUMN `user_secure`,
DROP COLUMN `user_lastip`,
DROP COLUMN `user_welcome_template`,
CHANGE COLUMN `user_id` `id` INT(11) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `user_name` `username` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `user_email` `email` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `user_pass` `password` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `user_level` `status` VARCHAR(255) NOT NULL DEFAULT 'New' ,
CHANGE COLUMN `user_onwikiname` `onwikiname` VARCHAR(255) NULL ,
CHANGE COLUMN `user_welcome_sig` `welcome_sig` VARCHAR(4096) NOT NULL DEFAULT '' ,
CHANGE COLUMN `user_lastactive` `lastactive` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
CHANGE COLUMN `user_forcelogout` `forcelogout` INT(3) NOT NULL DEFAULT '0' ,
CHANGE COLUMN `user_checkuser` `checkuser` INT(1) NOT NULL DEFAULT '0' ,
CHANGE COLUMN `user_identified` `identified` INT(1) UNSIGNED NOT NULL DEFAULT '0' ,
CHANGE COLUMN `user_welcome_templateid` `welcome_template` INT(11) NULL ,
CHANGE COLUMN `user_abortpref` `abortpref` TINYINT(4) NOT NULL DEFAULT '0' ,
CHANGE COLUMN `user_confirmationdiff` `confirmationdiff` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
CHANGE COLUMN `user_emailsig` `emailsig` BLOB NOT NULL,
RENAME TO `user`;

CREATE OR REPLACE VIEW acc_user AS
SELECT 
	id as user_id,
	username as user_name,
	email as user_email,
	password as user_password,
	status as user_level,
	onwikiname as user_onwikiname,
	welcome_sig as user_welcome_sig,
	"" as user_welcome_template, 
	lastactive as user_lastactive,
	"0.0.0.0" as user_lastip,
	forcelogout as user_forcelogout,
	"1" as user_secure,
	checkuser as user_checkuser,
	identified as user_identified,
	welcome_template as user_welcome_templateid,
	abortpref as user_abortpref,
	confirmationdiff as user_confirmationdiff,
	emailsig as user_emailsig,
	oauthrequesttoken,
	oauthrequestsecret,
	oauthaccesstoken,
	oauthaccesssecret,
	oauthidentitycache
FROM user;