CREATE OR REPLACE VIEW `ban` AS
SELECT `acc_ban`.`ban_id` AS id,
    `acc_ban`.`ban_type` AS type,
    `acc_ban`.`ban_target` AS target,
    `acc_ban`.`ban_user` AS user,
    `acc_ban`.`ban_reason` AS reason,
    `acc_ban`.`ban_date` AS date,
    `acc_ban`.`ban_duration` AS duration,
    `acc_ban`.`ban_active` AS active
FROM `acc_ban`;
