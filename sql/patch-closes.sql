CREATE OR REPLACE
VIEW `closes` AS
    select 
        concat('Closed ', `acc_emails`.`mail_id`) AS `closed`,
        `acc_emails`.`mail_desc` AS `mail_desc`
    from
        `acc_emails`
    where
        (`acc_emails`.`mail_type` = 'Message') 
    union select 'Closed 0' AS `Closed 0`, 'Dropped' AS `Dropped` 
    union select 
        'Closed custom' AS `Closed custom`,
        'Closed custom' AS `Closed custom`
    
    union select 
        'Closed custom-n' AS `Closed custom-n`,
        'Closed custom - Not created' AS `Closed custom - Not created`
    
    union select 
        'Closed custom-y' AS `Closed custom-y`,
        'Closed custom - Created' AS `Closed custom - Created`;

