DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
DELIMITER ';;'
CREATE PROCEDURE SCHEMA_UPGRADE_SCRIPT() BEGIN
    -- -------------------------------------------------------------------------
    -- Developers - set the number of the schema patch here!
    -- -------------------------------------------------------------------------
    DECLARE patchversion INT DEFAULT 45;
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

    -- Add new column and foreign keys
    ALTER TABLE request
        ADD COLUMN domain INT UNSIGNED NULL,
        ADD CONSTRAINT request_fk_domain_domain FOREIGN KEY (domain) REFERENCES domain (id) ON DELETE CASCADE;
    ALTER TABLE emailtemplate
        ADD COLUMN domain INT UNSIGNED NULL,
        ADD CONSTRAINT emailtemplate_fk_domain_domain FOREIGN KEY (domain) REFERENCES domain (id) ON DELETE CASCADE;
    ALTER TABLE welcometemplate
        ADD COLUMN domain INT UNSIGNED NULL,
        ADD CONSTRAINT welcometemplate_fk_domain_domain FOREIGN KEY (domain) REFERENCES domain (id) ON DELETE CASCADE;
    ALTER TABLE userrole
        ADD COLUMN domain INT UNSIGNED NULL,
        ADD CONSTRAINT userrole_fk_domain_domain FOREIGN KEY (domain) REFERENCES domain (id) ON DELETE CASCADE;
    ALTER TABLE log
        ADD COLUMN domain INT UNSIGNED NULL,
        ADD CONSTRAINT log_fk_domain_domain FOREIGN KEY (domain) REFERENCES domain (id) ON DELETE CASCADE;
    ALTER TABLE jobqueue
        ADD COLUMN domain INT UNSIGNED NULL,
        ADD CONSTRAINT jobqueue_fk_domain_domain FOREIGN KEY (domain) REFERENCES domain (id) ON DELETE CASCADE;

    -- Update unique indexes
    ALTER TABLE emailtemplate
        DROP KEY name,
        ADD CONSTRAINT emailtemplate_uidx_domain_name UNIQUE (domain, name);
    ALTER TABLE userrole
        DROP KEY userrole_user_role_uindex,
        ADD CONSTRAINT userrole_uidx_user_role_domain UNIQUE (user, role, domain);

    -- Populate column
    UPDATE request SET domain = 1;
    UPDATE emailtemplate SET domain = 1;
    UPDATE welcometemplate SET domain = 1;
    UPDATE userrole SET domain = 1;
    UPDATE log SET domain = 1
        WHERE objecttype IN (
            'Comment', 'EmailTemplate', 'JobQueue', 'Request', 'RequestForm', 'RequestQueue', 'WelcomeTemplate'
        );
    UPDATE log SET domain = 1 WHERE action = 'RoleChange' AND objecttype = 'User';
    UPDATE jobqueue SET domain = 1;

    -- Mark as not nullable
    ALTER TABLE request MODIFY COLUMN domain INT UNSIGNED NOT NULL;
    ALTER TABLE emailtemplate MODIFY COLUMN domain INT UNSIGNED NOT NULL;
    ALTER TABLE welcometemplate MODIFY COLUMN domain INT UNSIGNED NOT NULL;
    ALTER TABLE userrole MODIFY COLUMN domain INT UNSIGNED NOT NULL;
    -- Skip log table; some rows can have a null domain for globally-relevant log entries.
    ALTER TABLE jobqueue MODIFY COLUMN domain INT UNSIGNED NOT NULL;

    -- -------------------------------------------------------------------------
    -- finally, update the schema version to indicate success
    # noinspection SqlWithoutWhere
    UPDATE schemaversion SET version = patchversion;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;