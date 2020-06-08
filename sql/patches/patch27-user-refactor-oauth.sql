DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
DELIMITER ';;'
CREATE PROCEDURE SCHEMA_UPGRADE_SCRIPT() BEGIN
    -- -------------------------------------------------------------------------
    -- Developers - set the number of the schema patch here!
    -- -------------------------------------------------------------------------
    DECLARE patchversion INT DEFAULT 27;
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

    CREATE TABLE oauthtoken
    (
        id INT PRIMARY KEY AUTO_INCREMENT,
        updateversion INT NOT NULL DEFAULT 0,
        user INT NOT NULL,
        token VARCHAR(45),
        secret VARCHAR(45),
        type VARCHAR(10) NOT NULL,
        expiry DATETIME DEFAULT NULL,
        CONSTRAINT oauthtoken_user_id_fk FOREIGN KEY (user) REFERENCES user (id),
        UNIQUE INDEX oauthtoken_user_type_uindex (user, type)
    ) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE credential
    (
        id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
        updateversion INT NOT NULL DEFAULT 0,
        user INT NOT NULL,
        factor INT DEFAULT 1 NOT NULL,
        type VARCHAR(45) DEFAULT 'password' NOT NULL,
        data VARCHAR(255),
        version INT(11) DEFAULT '1' NOT NULL,
        disabled INT(11) DEFAULT '0' NOT NULL,
        CONSTRAINT credential_user_id_fk FOREIGN KEY (user) REFERENCES user (id),
        UNIQUE INDEX credential_user_type_uindex (user, type)
    ) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE oauthidentity
    (
        id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
        updateversion INT NOT NULL DEFAULT 0,
        user INT NOT NULL,
        iss VARCHAR(45) NOT NULL,
        sub INT NOT NULL,
        aud VARCHAR(45) NOT NULL,
        exp VARCHAR(10) NOT NULL,
        iat VARCHAR(10) NOT NULL,
        username VARCHAR(255) NOT NULL,
        editcount INT NOT NULL,
        confirmed_email INT NOT NULL,
        blocked INT NOT NULL,
        registered VARCHAR(14) NOT NULL,
        checkuser INT NOT NULL,
        grantbasic INT NOT NULL,
        grantcreateaccount INT NOT NULL,
        granthighvolume INT NOT NULL,
        grantcreateeditmovepage INT NOT NULL,
        CONSTRAINT oauthidentity_user_id_fk FOREIGN KEY (user) REFERENCES user (id)
    ) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;

    INSERT INTO credential (user, type, factor, data)
        SELECT id, 'password', 1, password FROM user;

    UPDATE credential
    SET
        version  = CASE
                   WHEN data IS NULL THEN 1
                   WHEN data = 'disabled password' THEN 1
                   WHEN substr(data, 1, 5) = ':2:x:' THEN 2
                   ELSE 1
                   END,
        data     = CASE
                   WHEN data IS NULL THEN NULL
                   WHEN data = 'disabled password' THEN NULL
                   WHEN substr(data, 1, 5) = ':2:x:' THEN substr(data, 6)
                   ELSE data
                   END,
        updateversion = updateversion + 1
    WHERE type = 'password';

    INSERT INTO oauthtoken (user, token, secret, type, expiry)
        SELECT id, oauthrequesttoken, oauthrequestsecret, 'request', DATE_ADD(NOW(), INTERVAL 1 DAY)
        FROM user
        WHERE oauthrequesttoken IS NOT NULL or oauthrequestsecret IS NOT NULL;

    INSERT INTO oauthtoken (user, token, secret, type)
        SELECT id, oauthaccesstoken, oauthaccesssecret, 'access'
        FROM user
        WHERE oauthaccesstoken IS NOT NULL or oauthaccesssecret IS NOT NULL;

    alter table user
        drop column password,
        drop column oauthaccesssecret,
        drop column oauthaccesstoken,
        drop column oauthrequestsecret,
        drop column oauthrequesttoken,
        drop column oauthidentitycache;

    -- -------------------------------------------------------------------------
    -- finally, update the schema version to indicate success
    UPDATE schemaversion SET version = patchversion;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;