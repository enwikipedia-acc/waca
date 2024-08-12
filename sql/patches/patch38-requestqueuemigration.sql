/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
DELIMITER ';;'
CREATE PROCEDURE SCHEMA_UPGRADE_SCRIPT() BEGIN
    -- -------------------------------------------------------------------------
    -- Developers - set the number of the schema patch here!
    -- -------------------------------------------------------------------------
    DECLARE patchversion INT DEFAULT 38;
    -- -------------------------------------------------------------------------
    -- working variables
    DECLARE currentschemaversion INT DEFAULT 0;
    DECLARE lastversion INT;
	
    -- check the schema has a version table
    IF NOT EXISTS (SELECT * FROM information_schema.tables WHERE table_name = 'schemaversion' AND table_schema = DATABASE()) THEN
        SIGNAL SQLSTATE '45000' SET message_text = 'Please ensure patches are run in order! This database does not have a schemaversion table.';
    END IF;
    
    -- get the current version
    SELECT version INTO currentschemaversion FROM schemaversion;
    
    -- check schema is not ahead of this patch
    IF currentschemaversion >= patchversion THEN
        SIGNAL SQLSTATE '45000' SET message_text = 'This patch has already been applied!';
    END IF;
    
    -- check schema is up-to-date
    SET lastversion = patchversion - 1;
    IF currentschemaversion != lastversion THEN
		SET @message_text = CONCAT('Please ensure patches are run in order! This patch upgrades to version ', patchversion, ', but the database is not version ', lastversion);
        SIGNAL SQLSTATE '45000' SET message_text = @message_text;
    END IF;

    -- -------------------------------------------------------------------------
    -- Developers - put your upgrade statements here!
    -- -------------------------------------------------------------------------

    -- collation fixes
    alter table requestqueue
        modify column apiname varchar(20) COLLATE utf8mb4_unicode_520_ci not null,
        modify column displayname varchar(100) COLLATE utf8mb4_unicode_520_ci not null,
        modify column header varchar(100) COLLATE utf8mb4_unicode_520_ci not null,
        modify column help text COLLATE utf8mb4_unicode_520_ci null,
        modify column logname varchar(50) COLLATE utf8mb4_unicode_520_ci not null,
        modify column legacystatus varchar(40) COLLATE utf8mb4_unicode_520_ci not null;

    alter table domain
        modify column shortname varchar(20) COLLATE utf8mb4_unicode_520_ci not null,
        modify column longname varchar(255) COLLATE utf8mb4_unicode_520_ci not null,
        modify column wikiarticlepath varchar(255) COLLATE utf8mb4_unicode_520_ci not null,
        modify column wikiapipath varchar(255) COLLATE utf8mb4_unicode_520_ci not null,
        modify column defaultlanguage varchar(10) COLLATE utf8mb4_unicode_520_ci not null default 'en',
        modify column emailsender varchar(255) COLLATE utf8mb4_unicode_520_ci not null,
        modify column notificationtarget varchar(255) COLLATE utf8mb4_unicode_520_ci null;

    alter table requestform
        modify column name varchar(255) COLLATE utf8mb4_unicode_520_ci not null,
        modify column publicendpoint varchar(64) COLLATE utf8mb4_unicode_520_ci not null,
        modify column formcontent longtext COLLATE utf8mb4_unicode_520_ci not null;

    -- request table - queue/status columns
    alter table request
        add queue int unsigned null after status,
        add constraint request_requestqueue_id_fk foreign key (queue) references requestqueue (id);

    update request r
    set queue = (select id from requestqueue rq where rq.legacystatus = r.status)
    where 1 = 1;

    update request r set status = 'Open' where queue is not null and status not in ('Closed', 'JobQueue', 'Hospital');

    -- email template - defer target column
    alter table emailtemplate
        add queue int unsigned null after defaultaction,
        add constraint emailtemplate_requestqueue_id_fk foreign key (queue) references requestqueue (id);

    update emailtemplate et
    set queue = (select id from requestqueue rq where rq.legacystatus = et.defaultaction)
    where 1 = 1;

    update emailtemplate set defaultaction = 'defer' where queue is not null;
    update emailtemplate set defaultaction = 'none' where defaultaction is null;

    alter table emailtemplate
        add constraint emailtemplate_defaultaction check (defaultaction in ('created', 'not created', 'defer', 'none'));

    -- ban table - defer target
    alter table ban
        add column targetqueue int unsigned null after action,
        add constraint ban_requestqueue_id_fk foreign key (targetqueue) references requestqueue (id);

    # noinspection SqlResolve @ column/"actiontarget"
    update ban b set b.updateversion = b.updateversion + 1,
                     b.targetqueue = (select rq.id from requestqueue rq where rq.legacystatus = b.actiontarget)
    where b.actiontarget is not null and b.action = 'defer';

    # noinspection SqlResolve @ column/"actiontarget"
    alter table ban
        drop column actiontarget;

    alter table ban
        add constraint ban_action check (action in ('block', 'drop', 'defer', 'none')),
        add constraint ban_targetqueue check (case when action = 'defer' and targetqueue is null then false else true end);

    alter table requestqueue drop column legacystatus;

    -- -------------------------------------------------------------------------
    -- finally, update the schema version to indicate success
    UPDATE schemaversion SET version = patchversion;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;