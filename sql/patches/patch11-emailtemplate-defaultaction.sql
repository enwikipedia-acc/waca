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
    DECLARE patchversion INT DEFAULT 11;
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

    ALTER TABLE `emailtemplate`
    ADD COLUMN `defaultaction` VARCHAR(45) NULL DEFAULT NULL AFTER `preloadonly`;

    UPDATE `emailtemplate`
    SET `defaultaction` = 'created'
    WHERE oncreated = 1;

    UPDATE `emailtemplate`
    SET `defaultaction` = 'not created'
    WHERE oncreated = 0;

    ALTER TABLE `emailtemplate`
    CHANGE COLUMN `oncreated` `oncreated` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Deprecated - see defaultaction' ,
    CHANGE COLUMN `defaultaction` `defaultaction` VARCHAR(45) NULL COMMENT 'The default action to take when this template is used for custom closes' ;

    -- -------------------------------------------------------------------------
    UPDATE schemaversion SET version = patchversion;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;