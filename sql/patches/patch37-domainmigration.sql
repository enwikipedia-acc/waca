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
    DECLARE patchversion INT DEFAULT 37;
    DECLARE userCount INT;
    DECLARE initialUser VARCHAR(255);
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

    SELECT username INTO initialUser FROM user WHERE id = 1;
    SELECT COUNT(*) INTO userCount FROM user;

    IF initialUser = 'Admin' AND userCount = 1 THEN
        INSERT INTO domain (shortname, longname, wikiarticlepath, wikiapipath, enabled, defaultclose, defaultlanguage, emailsender, notificationtarget)
        VALUES ('enwiki', 'English Wikipedia', 'https://en.wikipedia.org/wiki/', 'https://en.wikipedia.org/w/api.php', 1, 1, 'en', 'accounts-enwiki-l@lists.wikimedia.org', 1);

        INSERT INTO requestqueue (enabled, isdefault, domain, apiname, displayname, header, help, logname, legacystatus)
        VALUES (1, 1, 1, 'open', 'users', 'Open requests', null, 'users', 'Open');

        INSERT INTO requestqueue (enabled, isdefault, defaultantispoof, defaulttitleblacklist, domain, apiname, displayname, header, help, logname, legacystatus)
        VALUES (1, 0, 1, 1, 1, 'admin', 'flagged users', 'Flagged user needed', 'This queue lists the requests which require a user with the accountcreator flag to create.\n\nIf creation is determined to be the correct course of action, requests here will require the overriding the AntiSpoof checks or the title blacklist in order to create. It is recommended to try to create the account *without* checking the flags to validate the results of the AntiSpoof and/or title blacklist hits.', 'flagged users', 'Flagged users');

        INSERT INTO requestqueue (enabled, isdefault, domain, apiname, displayname, header, help, logname, legacystatus)
        VALUES (1, 0, 1, 'checkuser', 'checkusers', 'Checkuser needed', null, 'checkusers', 'Checkuser');

        # noinspection SqlWithoutWhere
        UPDATE schemaversion SET version = patchversion;
    ELSE
        SIGNAL SQLSTATE '45000' SET message_text = 'Please run the MigrateToDomains.php maintenance job to perform the migration!';
    END IF;

END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;