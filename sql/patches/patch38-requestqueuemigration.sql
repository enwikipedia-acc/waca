-- -----------------------------------------------------------------------------
-- Hey!
-- 
-- This is a new patch-creation script which SHOULD stop double-patching and
-- running patches out-of-order.
--
-- If you're running patches, please close this file, and run this from the 
-- command line:
--   $ mysql -u USERNAME -p SCHEMA < patchXX-this-file.sql
-- where:
--      USERNAME = a user with CREATE/ALTER access to the schema
--      SCHEMA = the schema to run the changes against
--      patch-XX-this-file.sql = this file
--
-- If you are writing patches, you need to copy this template to a numbered 
-- patch file, update the patchversion variable, and add the SQL code to upgrade
-- the database where indicated below.

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

END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;