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
    DECLARE patchversion INT DEFAULT 50;
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
    
    UPDATE user SET status = 'Deactivated' WHERE status IN ('Suspended', 'Declined');
    UPDATE log SET action = 'DeactivatedUser' WHERE action IN ('Suspended', 'Declined', 'Deactivated') AND objecttype = 'User';
    
    -- -------------------------------------------------------------------------
    -- finally, update the schema version to indicate success
    # noinspection SqlWithoutWhere
    UPDATE schemaversion SET version = patchversion;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;