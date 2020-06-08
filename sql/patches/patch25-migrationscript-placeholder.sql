-- Hey!
-- This patch script exists as a placeholder for a PHP-based migration maintenance job.
-- If you are a database build job, this job should succeed without issues
-- If you have an existing database, please use the PHP script instead, running this script will throw an error
-- If you are neither of the above, please figure out what to do yourself.

DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
DELIMITER ';;'
CREATE PROCEDURE SCHEMA_UPGRADE_SCRIPT() BEGIN
    DECLARE patchversion INT DEFAULT 25;
    DECLARE currentschemaversion INT DEFAULT 0;
    DECLARE lastversion INT;
    DECLARE userCount INT;
    DECLARE initialUser VARCHAR(255);
	
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

    SELECT username INTO initialUser FROM user WHERE id = 1;
    SELECT COUNT(*) INTO userCount FROM user;

    -- Only user to exist is the initial "Admin" user, so this is probably the build job. Silently handle this.
    IF initialUser = 'Admin' AND userCount = 1 THEN
        INSERT INTO userrole (user, role, updateversion)
        VALUES (1, 'admin', 0), (1, 'toolRoot', 0);

        UPDATE schemaversion SET version = patchversion;
    ELSE
        IF NOT EXISTS (SELECT * FROM user WHERE status = 'Active') THEN
            SIGNAL SQLSTATE '45000' SET message_text = 'Please run the MigrateToRoles.php job to perform the migration!';
        ELSE
            SIGNAL SQLSTATE '45000' SET message_text = 'Something unexpected happened, please fix manually. Proceeding further will cause data loss.';
        END IF;
    END IF;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
