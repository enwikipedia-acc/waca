ALTER TABLE acc_user
CHANGE COLUMN user_welcome_templateid user_welcome_templateid INT(11) NULL DEFAULT NULL ;

UPDATE acc_user SET user_welcome_templateid = NULL WHERE user_welcome_templateid = 0;