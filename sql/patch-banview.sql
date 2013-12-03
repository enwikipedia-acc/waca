CREATE OR REPLACE VIEW `ban` AS
SELECT `acc_ban`.`ban_id`,
    `acc_ban`.`ban_type`,
    `acc_ban`.`ban_target`,
    `acc_ban`.`ban_user`,
    `acc_ban`.`ban_reason`,
    `acc_ban`.`ban_date`,
    `acc_ban`.`ban_duration`,
    `acc_ban`.`ban_active`
FROM `acc_ban`;
