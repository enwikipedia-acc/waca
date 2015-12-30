DROP PROCEDURE IF EXISTS schema_change;

DELIMITER ';;'
CREATE PROCEDURE schema_change() BEGIN
    IF EXISTS (SELECT * FROM information_schema.columns WHERE table_name = 'user' AND column_name = 'root' AND table_schema = database()) THEN
        ALTER TABLE USER 
            DROP COLUMN `root`,
            CHANGE COLUMN checkuser checkuser INT(1) NOT NULL DEFAULT '0' COMMENT '';
    END IF;

    CREATE TABLE `schemaversion` (
      `version` INT NOT NULL DEFAULT 10,
      `updated` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`version`))
    COMMENT = 'Current schema version for use by update scripts';
    
    INSERT INTO schemaversion (version) VALUES (10);
END;;

DELIMITER ';'
CALL schema_change();

DROP PROCEDURE IF EXISTS schema_change;