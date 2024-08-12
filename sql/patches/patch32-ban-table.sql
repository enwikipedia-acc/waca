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
    DECLARE patchversion INT DEFAULT 32;
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

    -- initial datafix
    update ban set
       date = regexp_replace(date, '^([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2})-([0-9]{2})-([0-9]{2})', '\\1:\\2:\\3')
    where date like '____-__-__ __-__-__';

    -- add new columns with the correct format, default null
    alter table ban
        change column user     user_old     varchar(255)  not null,
        change column date     date_old     varchar(1024) not null,
        change column duration duration_old varchar(50)   not null;
    alter table ban
        add column user     int(11)   null,
        add column date     timestamp null,
        add column duration int(11)   null;

    -- migrate the data from the old column to the new
    update ban set
       duration = case
                      when ban.duration_old = '-1' then null
                      when ban.duration_old = '1' then null
                      else cast(duration_old as integer) end,
       user = cast(user_old as integer),
       date = case when ban.date_old = '0000-00-00 00:00:00' then null else cast(ban.date_old as datetime) end;

    -- drop the old columns, configure new columns correctly.
    alter table ban
        modify column user int(11) not null,
        modify column date timestamp null,
        modify column duration int(11) null,
        drop column date_old,
        drop column duration_old,
        drop column user_old;

    -- add missing FK0
    alter table ban
        add constraint ban_user_id_fk
            foreign key (user) references user (id);

    -- -------------------------------------------------------------------------
    -- finally, update the schema version to indicate success
    UPDATE schemaversion SET version = patchversion;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
