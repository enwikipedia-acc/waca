DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
DELIMITER ';;'
CREATE PROCEDURE SCHEMA_UPGRADE_SCRIPT() BEGIN
  DECLARE patchversion INT DEFAULT 19;

  -- -------------------------------------------------------------------------
  -- working variables
  DECLARE currentschemaversion INT DEFAULT 0;
  DECLARE lastversion INT;
  DECLARE messageCount INT;

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

  -- ----------------------------------
  -- data migration

  SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;
  START TRANSACTION;

  -- sanity check
  SELECT COUNT(*) INTO messageCount FROM interfacemessage WHERE type = 'Message';

  IF messageCount <> 7 THEN
    -- this script assumes that the message IDs are the same as they were in production as of 23rd March.
    -- if this is different, we need to revisit this script.
    SET @message_text = CONCAT('Message count is wrong, data migration has failed.');
    ROLLBACK;
    SIGNAL SQLSTATE '45000' SET message_text = @message_text;
  END IF;

  -- upgrade old log entries to reflect new location of email messages
  UPDATE log l
    LEFT JOIN interfacemessage i ON l.objectid = i.id
    LEFT JOIN emailtemplate e ON e.id = CASE
                                        WHEN i.id < 6 THEN i.id
                                        WHEN i.id = 26 THEN 6 -- these mappings are from production. Data import scripts
                                        WHEN i.id = 30 THEN 7 -- should set these correctly.
                                        ELSE NULL END
  SET objectid = e.id, objecttype = 'EmailTemplate', action = 'EditedEmail', comment = NULL
  WHERE l.objecttype = 'InterfaceMessage'
        AND l.action = 'Edited'
        AND i.type = 'Message'
        AND e.id IS NOT NULL;

  -- drop old interface messages from the table that have been migrated to email templates
  DELETE FROM interfacemessage WHERE id IN (1, 2, 3, 4, 5, 26, 30) AND type = 'Message';

  SELECT ROW_COUNT() INTO messageCount FROM DUAL;

  IF messageCount <> 7 THEN
    -- this script assumes that the message IDs are the same as they were in production as of 23rd March.
    -- if this is different, we need to revisit this script.
    SET @message_text = CONCAT('Deletion count is wrong, data migration has failed.');
    ROLLBACK;
    SIGNAL SQLSTATE '45000' SET message_text = @message_text;
  END IF;

  COMMIT;
  -- Finished data migration, continue with schema changes
  -- ---------------------------------------

  -- these cache tables have dataobjects attached, not sure if they're strictly needed or not.
  ALTER TABLE antispoofcache ADD updateversion INT NOT NULL DEFAULT 0;
  ALTER TABLE geolocation ADD updateversion INT NOT NULL DEFAULT 0;
  ALTER TABLE rdnscache ADD updateversion INT NOT NULL DEFAULT 0;

  -- main dataobjects
  ALTER TABLE ban ADD updateversion INT NOT NULL DEFAULT 0;
  ALTER TABLE comment ADD updateversion INT NOT NULL DEFAULT 0;
  ALTER TABLE emailtemplate ADD updateversion INT NOT NULL DEFAULT 0;
  ALTER TABLE interfacemessage ADD updateversion INT NOT NULL DEFAULT 0;
  ALTER TABLE log ADD updateversion INT NOT NULL DEFAULT 0;
  ALTER TABLE request ADD updateversion INT NOT NULL DEFAULT 0;
  ALTER TABLE user ADD updateversion INT NOT NULL DEFAULT 0;
  ALTER TABLE welcometemplate ADD updateversion INT NOT NULL DEFAULT 0;

  -- drop legacy column that's unused now
  ALTER TABLE interfacemessage DROP updatecounter;

  -- -------------------------------------------------------------------------
  -- finally, update the schema version to indicate success
  UPDATE schemaversion SET version = patchversion;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;