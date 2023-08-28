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
    DECLARE patchversion INT DEFAULT 43;
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

    -- unused column
    # noinspection SqlResolve
    ALTER TABLE user
        DROP welcome_sig;

    CREATE TABLE userpreference
    (
        id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user          INT UNSIGNED   NOT NULL,
        domain        INT UNSIGNED   NULL COMMENT 'The domain this specific preference is for',
        preference    VARCHAR(255)   NOT NULL,
        value         VARBINARY(255) NULL,
        global        TINYINT UNSIGNED AS (CASE WHEN domain IS NULL THEN 1 END) COMMENT 'Is this globally-defined?',
        updateversion INT UNSIGNED NOT NULL DEFAULT 0,
        CONSTRAINT userpreference_user_id_fk FOREIGN KEY (user) REFERENCES user (id),
        CONSTRAINT userpreference_domain_id_fk FOREIGN KEY (domain) REFERENCES domain (id)
    ) ENGINE = InnoDB COMMENT 'Holds user preferences';

    ALTER TABLE userpreference
        ADD CONSTRAINT userpreference_userglobal_uniq UNIQUE (user, preference, global),
        ADD CONSTRAINT userpreference_userdomain_uniq UNIQUE (user, preference, domain);

    # noinspection SqlResolve
    INSERT INTO userpreference (user, domain, preference, value)
    SELECT u.id as user, 1 as domain, 'welcomeTemplate' as preference, u.welcome_template as value FROM user u WHERE u.welcome_template IS NOT NULL
    UNION ALL
    SELECT u.id, 1, 'skipJsAbort', u.abortpref FROM user u -- NN column
    UNION ALL
    SELECT u.id, 1, 'emailSignature', u.emailsig FROM user u WHERE u.emailsig IS NOT NULL
    UNION ALL
    SELECT u.id, 1, 'creationMode', u.creationmode FROM user u -- NN column
    UNION ALL
    SELECT u.id, null, 'skin', u.skin FROM user u -- NN column, global setting
    ;

    COMMIT;

    # noinspection SqlResolve
    ALTER TABLE user
        DROP CONSTRAINT user_welcometemplate_id_fk,
        DROP welcome_template,
        DROP abortpref,
        DROP emailsig,
        DROP creationmode,
        DROP skin
    ;

    -- -------------------------------------------------------------------------
    -- finally, update the schema version to indicate success
    # noinspection SqlWithoutWhere
    UPDATE schemaversion SET version = patchversion;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
