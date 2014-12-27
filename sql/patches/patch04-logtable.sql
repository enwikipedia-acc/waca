ALTER TABLE `acc_log` 
CHANGE COLUMN `log_id` `id` INT(11) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `log_action` `action` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `log_time` `timestamp` DATETIME NOT NULL ,
CHANGE COLUMN `log_cmt` `comment` BLOB NULL ,
ADD COLUMN `objectid` INT(11) NULL AFTER `log_pend`,
ADD COLUMN `objecttype` VARCHAR(45) NULL AFTER `objectid`,
ADD COLUMN `user` INT NULL AFTER `log_user`,
DROP INDEX `log_pend_idx` ,
DROP INDEX `acc_log_action_idx` , 
RENAME TO `log` ;

UPDATE log SET objectid = cast(log_pend as unsigned integer);

UPDATE log SET objecttype = CASE
        WHEN action IN ('Email Confirmed' , 'EditComment-r', 'Closed') THEN 'Request'
        WHEN action IN ('Banned' , 'Unbanned') THEN 'Ban'
        WHEN action IN ('Declined' , 'Prefchange', 'Renamed', 'Suspended', 'Approved', 'Promoted', 'Demoted') THEN 'User'
        WHEN action = 'Edited' THEN 'InterfaceMessage'
        WHEN action = 'EditComment-c' THEN 'Comment'
        WHEN action LIKE '%Reserve%' THEN 'Request'
        WHEN action LIKE '%Email' THEN 'Email'
        WHEN action LIKE '%Template' THEN 'Template'
        WHEN action LIKE 'Closed %' THEN 'Request'
        WHEN action LIKE 'Deferred to %' THEN 'Request'
    END;

update log
inner join user on user.username = log.log_user
set user = user.id;

update log set user = -1 where action = "Email Confirmed";

update log set user = -1 where log_user = "Clean-up script";

ALTER TABLE `log` 
DROP COLUMN `log_user`,
DROP COLUMN `log_pend`,
CHANGE COLUMN `objectid` `objectid` INT(11) NOT NULL ,
CHANGE COLUMN `objecttype` `objecttype` VARCHAR(45) NOT NULL ,
CHANGE COLUMN `user` `user` INT(11) NOT NULL ;

create or replace view acc_log as 
SELECT 
    l.id AS log_id,
    l.objectid as log_pend,
    case when action = "Email Confirmed" then r.name else u.username end as log_user,
    action as log_action,
    timestamp as log_time,
    l.comment as log_cmt
FROM log l
left join user u on l.user = u.id
left join request r on l.objectid = r.id
