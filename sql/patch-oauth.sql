ALTER TABLE `acc_user` 
	ADD COLUMN `oauthrequesttoken` VARCHAR(45) NULL AFTER `user_emailsig`,
	ADD COLUMN `oauthrequestsecret` VARCHAR(45) NULL AFTER `oauthrequesttoken`,
	ADD COLUMN `oauthaccesstoken` VARCHAR(45) NULL AFTER `oauthrequestsecret`,
	ADD COLUMN `oauthaccesssecret` VARCHAR(45) NULL AFTER `oauthaccesstoken`;

CREATE OR REPLACE VIEW `user` AS
    SELECT 
        `acc_user`.`user_id` AS `id`,
        `acc_user`.`user_name` AS `username`,
        `acc_user`.`user_email` AS `email`,
        `acc_user`.`user_pass` AS `password`,
        `acc_user`.`user_level` AS `status`,
        `acc_user`.`user_onwikiname` AS `onwikiname`,
        `acc_user`.`user_welcome_sig` AS `welcome_sig`,
        `acc_user`.`user_lastactive` AS `lastactive`,
        `acc_user`.`user_forcelogout` AS `forcelogout`,
        `acc_user`.`user_secure` AS `secure`,
        `acc_user`.`user_checkuser` AS `checkuser`,
        `acc_user`.`user_identified` AS `identified`,
        `acc_user`.`user_welcome_templateid` AS `welcome_template`,
        `acc_user`.`user_abortpref` AS `abortpref`,
        `acc_user`.`user_confirmationdiff` AS `confirmationdiff`,
        `acc_user`.`user_emailsig` AS `emailsig`,        
		`acc_user`.`oauthrequesttoken` AS `oauthrequesttoken`,
        `acc_user`.`oauthrequestsecret` AS `oauthrequestsecret`,
        `acc_user`.`oauthaccesstoken` AS `oauthaccesstoken`,
        `acc_user`.`oauthaccesssecret` AS `oauthaccesssecret`		
    FROM
        `acc_user`;

ALTER TABLE `acc_user` 
	CHANGE COLUMN `user_onwikiname` `user_onwikiname` VARCHAR(255) NULL ;
