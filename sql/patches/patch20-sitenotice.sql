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
  DECLARE patchversion INT DEFAULT 20;
  -- -------------------------------------------------------------------------
  -- working variables
  DECLARE currentschemaversion INT DEFAULT 0;
  DECLARE lastversion INT;

  -- check the schema has a version table
  IF NOT EXISTS(SELECT *
                FROM information_schema.tables
                WHERE table_name = 'schemaversion' AND table_schema = DATABASE())
  THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Please ensure patches are run in order! This database does not have a schemaversion table.';
  END IF;

  -- get the current version
  SELECT version
  INTO currentschemaversion
  FROM schemaversion;

  -- check schema is not ahead of this patch
  IF currentschemaversion >= patchversion
  THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'This patch has already been applied!';
  END IF;

  -- check schema is up-to-date
  SET lastversion = patchversion - 1;
  IF currentschemaversion != lastversion
  THEN
    SET @message_text = CONCAT('Please ensure patches are run in order! This patch upgrades to version ', patchversion,
                               ', but the database is not version ', lastversion);
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = @message_text;
  END IF;

  -- -------------------------------------------------------------------------
  -- Developers - put your upgrade statements here!
  -- -------------------------------------------------------------------------

  -- ----------------------------------
  -- data migration

  SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;
  START TRANSACTION;

  DELETE FROM log
  WHERE objecttype = 'InterfaceMessage' AND objectid <> 31;
  UPDATE log
  SET objecttype = 'SiteNotice'
  WHERE objectid = 31 AND objecttype = 'InterfaceMessage';
  DELETE FROM interfacemessage
  WHERE id <> 31;
  UPDATE interfacemessage
  SET id = 1
  WHERE id = 31;

  COMMIT;
  -- Finished data migration, continue with schema changes
  -- ---------------------------------------

  ALTER TABLE interfacemessage DROP type, DROP description, RENAME TO sitenotice;

  -- drop some old unused views
  DROP VIEW IF EXISTS acc_emails;
  DROP VIEW IF EXISTS acc_trustedips;

  -- -------------------------------------------------------------------------
  -- finally, update the schema version to indicate success
  UPDATE schemaversion
  SET version = patchversion;
END;;

DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;