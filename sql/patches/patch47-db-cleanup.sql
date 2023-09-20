DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
DELIMITER ';;'
CREATE PROCEDURE SCHEMA_UPGRADE_SCRIPT() BEGIN
    -- -------------------------------------------------------------------------
    -- Developers - set the number of the schema patch here!
    -- -------------------------------------------------------------------------
    DECLARE patchversion INT DEFAULT 47;
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
    
    -- Stage 1: clean up bad charsets.
    -- This is potentially risky, but given we're only switching from utf8mb3 -> utf8mb4, we can 
    -- take some safe shortcuts.

    -- Start with the database
    -- Must use dynamic SQL here because you can't put function calls in as the object to alter.
    PREPARE dbStatement FROM CONCAT('ALTER DATABASE ', DATABASE(), ' CHARACTER SET = ''utf8mb4'' COLLATE = ''utf8mb4_unicode_520_ci''');
    EXECUTE dbStatement;
    DEALLOCATE PREPARE dbStatement;

    -- only ascii values in prod
    ALTER TABLE domain
        MODIFY COLUMN localdocumentation VARCHAR(255) COLLATE utf8mb4_unicode_520_ci NOT NULL 
            COMMENT 'link to the local version of WP:ACC/G',
        MODIFY COLUMN emailreplyaddress VARCHAR(255) COLLATE utf8mb4_unicode_520_ci NOT NULL 
            COMMENT 'the from/sender address of the mailing list, for users to reply with';

    -- only ascii values in prod
    ALTER TABLE requestdata
        MODIFY COLUMN type VARCHAR(10) COLLATE utf8mb4_unicode_520_ci NOT NULL 
            COMMENT 'The type of private data stored',
        MODIFY COLUMN name VARCHAR(50) COLLATE utf8mb4_unicode_520_ci NULL 
            COMMENT 'The name for key/value client hint data';

    -- empty in prod
    ALTER TABLE tornodecache
        MODIFY COLUMN ipaddr VARCHAR(45) COLLATE utf8mb4_unicode_520_ci NOT NULL 
            COMMENT 'IP Address of exit node',
        MODIFY COLUMN exitaddr VARCHAR(45) COLLATE utf8mb4_unicode_520_ci NOT NULL 
            COMMENT 'IP address exits to this IP';

    -- fixed set of ascii values
    ALTER TABLE userrole 
        MODIFY COLUMN role VARCHAR(20) COLLATE utf8mb4_unicode_520_ci NOT NULL 
            COMMENT 'Name of the role granted to this user';

    -- fixed set of ascii values
    ALTER TABLE userpreference 
        MODIFY COLUMN preference VARCHAR(45) COLLATE utf8mb4_unicode_520_ci NOT NULL 
            COMMENT 'Name of the preference';

    -- safe for ruwiki form; tested locally
    ALTER TABLE requestform 
        MODIFY COLUMN usernamehelp TEXT COLLATE utf8mb4_unicode_520_ci NOT NULL 
            COMMENT 'text below the username box',
        MODIFY COLUMN emailhelp TEXT COLLATE utf8mb4_unicode_520_ci NOT NULL 
            COMMENT 'text below the email box',
        MODIFY COLUMN commentshelp TEXT COLLATE utf8mb4_unicode_520_ci NOT NULL 
            COMMENT 'text below the comments box';

    -- generated data, safe to purge this and let it repopulate naturally
    TRUNCATE TABLE idcache;
    ALTER TABLE idcache
        MODIFY COLUMN onwikiusername varchar(255) COLLATE utf8mb4_bin NOT NULL;

    -- Unused table
    DROP TABLE IF EXISTS id;

    -- Change the overall default table collations
    ALTER TABLE credential COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE domain COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE idcache COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE jobqueue COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE netmask COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE oauthidentity COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE oauthtoken COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE requestdata COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE requestform COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE requestqueue COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE tornodecache COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE userdomain COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE userpreference COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE userrole COLLATE utf8mb4_unicode_520_ci;

    -- Stage 2: Fix any remaining collations which are incorrect

    -- Do the more basic collation updates for those missing utf8mb4_unicode_520_ci
    ALTER TABLE credential
        MODIFY COLUMN type varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'password';

    ALTER TABLE oauthtoken
        MODIFY COLUMN type varchar(10)  COLLATE utf8mb4_unicode_520_ci NOT NULL;

    ALTER TABLE jobqueue
        MODIFY COLUMN `task` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
        MODIFY COLUMN `status` varchar(10) COLLATE utf8mb4_unicode_520_ci DEFAULT 'ready',
        MODIFY COLUMN `error` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;

    -- Finally, use utf8mb4_bin for those which should be treated as binary data
    ALTER TABLE ban
        MODIFY COLUMN name varchar(512) COLLATE utf8mb4_bin DEFAULT NULL,
        MODIFY COLUMN email varchar(190) COLLATE utf8mb4_bin DEFAULT NULL,
        MODIFY COLUMN useragent text COLLATE utf8mb4_bin DEFAULT NULL;

    ALTER TABLE credential
        MODIFY COLUMN data text COLLATE utf8mb4_bin DEFAULT NULL;

    ALTER TABLE oauthidentity
        MODIFY COLUMN iss varchar(45) COLLATE utf8mb4_bin NOT NULL,
        MODIFY COLUMN aud varchar(45) COLLATE utf8mb4_bin NOT NULL,
        MODIFY COLUMN exp varchar(10) COLLATE utf8mb4_bin NOT NULL,
        MODIFY COLUMN iat varchar(10) COLLATE utf8mb4_bin NOT NULL,
        MODIFY COLUMN username varchar(255) COLLATE utf8mb4_bin NOT NULL,
        MODIFY COLUMN registered varchar(14) COLLATE utf8mb4_bin DEFAULT NULL 
            COMMENT 'Registration date, note early users don''t have one!';
    
    ALTER TABLE oauthtoken
        MODIFY COLUMN token varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
        MODIFY COLUMN secret varchar(45) COLLATE utf8mb4_bin DEFAULT NULL;

    -- Stage 3: Fix table storage engines
    ALTER TABLE applicationlog ENGINE=MyISAM;


    -- PART 2 --------------------------
    -- Renaming constraints

    ALTER TABLE ban
        DROP FOREIGN KEY ban_requestqueue_id_fk,
        DROP FOREIGN KEY ban_user_id_fk,
        DROP CONSTRAINT ban_action,
        DROP CONSTRAINT ban_active,
        DROP CONSTRAINT ban_targetqueue;

    ALTER TABLE ban
        RENAME INDEX ban_requestqueue_id_fk TO ban_fk_targetqueue_requestqueue,
        RENAME INDEX ban_user_id_fk TO ban_fk_user_user,
        RENAME INDEX idx_ban_active TO ban_idx_active_duration;

    ALTER TABLE ban
        ADD CONSTRAINT ban_fk_targetqueue_requestqueue
            FOREIGN KEY (targetqueue) REFERENCES requestqueue (id),
        ADD CONSTRAINT ban_fk_user_user
            FOREIGN KEY (user) REFERENCES user (id),
        ADD CONSTRAINT ban_check_action
            CHECK (`action` IN ('block', 'drop', 'defer', 'none')),
        ADD CONSTRAINT ban_check_active
            CHECK (`active` IN (0, 1)),
        ADD CONSTRAINT ban_check_targetqueue
            CHECK (CASE WHEN `action` = 'defer' AND `targetqueue` IS NULL THEN 0 ELSE 1 END);


    ALTER TABLE comment
        DROP FOREIGN KEY comment_user_id_fk,
        DROP FOREIGN KEY comment_request_id_fk;

    ALTER TABLE comment
        RENAME INDEX comment_user_id_fk TO comment_fk_user_user,
        RENAME INDEX idx_comment_request TO comment_fk_request_request,
        RENAME INDEX comment_flagged_index TO comment_idx_flagged;

    ALTER TABLE comment
        ADD CONSTRAINT comment_fk_user_user
            FOREIGN KEY (user) REFERENCES user (id),
        ADD CONSTRAINT comment_fk_request_request
            FOREIGN KEY (request) REFERENCES request (id);

    ALTER TABLE credential
        DROP FOREIGN KEY credential_user_id_fk,
        DROP CONSTRAINT credential_disabled;

    ALTER TABLE credential
        RENAME INDEX credential_user_type_uindex TO credential_uidx_user_type,
        ADD INDEX credential_fk_user_user (user);

    ALTER TABLE credential
        ADD CONSTRAINT credential_fk_user_user
            FOREIGN KEY (user) REFERENCES user (id),
        ADD CONSTRAINT credential_check_disabled
            CHECK (`disabled` IN (0, 1));

    ALTER TABLE domain
        DROP CONSTRAINT domain_emailtemplate_id_fk,
        DROP CONSTRAINT domain_enabled;

    ALTER TABLE domain
        RENAME INDEX domain_shortname_uindex TO domain_uidx_shortname,
        RENAME INDEX domain_wikiapipath_uniq TO domain_uidx_wikiapipath,
        RENAME INDEX domain_wikiarticlepath_uniq TO domain_uidx_wikiarticlepath,
        RENAME INDEX domain_emailtemplate_id_fk TO domain_fk_defaultclose_emailtemplate;

    ALTER TABLE domain
        ADD CONSTRAINT domain_fk_defaultclose_emailtemplate
            FOREIGN KEY (defaultclose) REFERENCES emailtemplate (id),
        ADD CONSTRAINT domain_check_enabled
            CHECK (`enabled` IN (0, 1));

    ALTER TABLE emailtemplate
        DROP FOREIGN KEY emailtemplate_requestqueue_id_fk,
        DROP CONSTRAINT emailtemplate_active,
        DROP CONSTRAINT emailtemplate_defaultaction;

    ALTER TABLE emailtemplate
        RENAME INDEX emailtemplate_requestqueue_id_fk TO emailtemplate_fk_queue_requestqueue;

    ALTER TABLE emailtemplate
        ADD CONSTRAINT emailtemplate_check_active
            CHECK (`active` IN (0, 1)),
        ADD CONSTRAINT emailtemplate_check_defaultaction
            CHECK (`defaultaction` IN ('created', 'not created', 'defer', 'none')),
        ADD CONSTRAINT emailtemplate_fk_queue_requestqueue
            FOREIGN KEY (queue) REFERENCES requestqueue (id);

    ALTER TABLE geolocation
        RENAME INDEX address_UNIQUE TO geolocation_uidx_address;

    ALTER TABLE idcache
        RENAME INDEX onwikiusername TO idcache_uidx_onwikiusername;

    ALTER TABLE jobqueue
        DROP FOREIGN KEY jobqueue_emailtemplate_id_fk,
        DROP FOREIGN KEY jobqueue_parent_id_fk,
        DROP FOREIGN KEY jobqueue_request_id_fk,
        DROP FOREIGN KEY jobqueue_user_id_fk,
        DROP CONSTRAINT jobqueue_acknowledged;

    ALTER TABLE jobqueue
        RENAME INDEX jobqueue_status_enqueue_index TO jobqueue_idx_status_enqueue,
        RENAME INDEX jobqueue_emailtemplate_id_fk TO jobqueue_fk_emailtemplate_emailtemplate,
        RENAME INDEX jobqueue_parent_id_fk TO jobqueue_fk_parent_jobqueue,
        RENAME INDEX jobqueue_request_id_fk TO jobqueue_fk_request_request,
        RENAME INDEX jobqueue_user_id_fk TO jobqueue_fk_user_user;

    ALTER TABLE jobqueue
        ADD CONSTRAINT jobqueue_fk_emailtemplate_emailtemplate
            FOREIGN KEY (emailtemplate) REFERENCES emailtemplate (id),
        ADD CONSTRAINT jobqueue_fk_parent_jobqueue
            FOREIGN KEY (parent) REFERENCES jobqueue (id),
        ADD CONSTRAINT jobqueue_fk_request_request
            FOREIGN KEY (request) REFERENCES request (id),
        ADD CONSTRAINT jobqueue_fk_user_user
            FOREIGN KEY (user) REFERENCES user (id),
        ADD CONSTRAINT jobqueue_check_acknowledged
            CHECK (`acknowledged` IN (0, 1));

    ALTER TABLE log
        RENAME INDEX log_idx_idtype TO log_idx_objecttype_objectid,
        RENAME INDEX log_idx_typeuseraction TO log_idx_objecttype_user_action;

    ALTER TABLE netmask
        DROP CONSTRAINT netmask_cidr,
        DROP CONSTRAINT netmask_protocol,
        DROP CONSTRAINT netmask_type;

    ALTER TABLE netmask
        ADD CONSTRAINT netmask_check_cidr
            CHECK (CASE WHEN `protocol` = 4 THEN `cidr` <= 32 WHEN `protocol` = 6 THEN `cidr` <= 128 ELSE 0 END),
        ADD CONSTRAINT netmask_check_protocol
            CHECK (`protocol` IN (4, 6)),
        ADD CONSTRAINT netmask_check_type
            CHECK (CASE WHEN `protocol` = 6 THEN `maskh` IS NOT NULL ELSE 1 END);

    ALTER TABLE oauthidentity
        DROP CONSTRAINT oauthidentity_blocked,
        DROP CONSTRAINT oauthidentity_checkuser,
        DROP CONSTRAINT oauthidentity_confirmed_email,
        DROP CONSTRAINT oauthidentity_grantbasic,
        DROP CONSTRAINT oauthidentity_grantcreateaccount,
        DROP CONSTRAINT oauthidentity_grantcreateeditmovepage,
        DROP CONSTRAINT oauthidentity_granthighvolume,
        DROP FOREIGN KEY oauthidentity_user_id_fk;

    ALTER TABLE oauthidentity
        RENAME INDEX oauthidentity_user_id_fk TO oauthidentity_fk_user_user;

    ALTER TABLE oauthidentity
        ADD CONSTRAINT oauthidentity_fk_user_user
            FOREIGN KEY (user) REFERENCES user (id),
        ADD CONSTRAINT oauthidentity_check_blocked
            CHECK (`blocked` IN (0, 1)),
        ADD CONSTRAINT oauthidentity_check_checkuser
            CHECK (`checkuser` IN (0, 1)),
        ADD CONSTRAINT oauthidentity_check_confirmed_email
            CHECK (`confirmed_email` IN (0, 1)),
        ADD CONSTRAINT oauthidentity_check_grantbasic
            CHECK (`grantbasic` IN (0, 1)),
        ADD CONSTRAINT oauthidentity_check_grantcreateaccount
            CHECK (`grantcreateaccount` IN (0, 1)),
        ADD CONSTRAINT oauthidentity_check_grantcreateeditmovepage
            CHECK (`grantcreateeditmovepage` IN (0, 1)),
        ADD CONSTRAINT oauthidentity_check_granthighvolume
            CHECK (`granthighvolume` IN (0, 1));

    ALTER TABLE oauthtoken
        DROP FOREIGN KEY oauthtoken_user_id_fk;

    ALTER TABLE oauthtoken
        RENAME INDEX oauthtoken_user_type_uindex TO oauthtoken_uidx_user_type,
        ADD INDEX oauthtoken_fk_user_user (user);

    ALTER TABLE oauthtoken
        ADD CONSTRAINT oauthtoken_fk_user_user
            FOREIGN KEY (user) REFERENCES user (id);

    ALTER TABLE rdnscache
        RENAME INDEX address_UNIQUE TO rdnscache_uidx_address;

    ALTER TABLE request
        DROP FOREIGN KEY request_requestform_id_fk,
        DROP FOREIGN KEY request_requestqueue_id_fk,
        DROP FOREIGN KEY request_user_id_fk;

    ALTER TABLE request
        RENAME INDEX request_requestform_id_fk TO request_fk_originform_requestform,
        RENAME INDEX request_requestqueue_id_fk TO request_fk_queue_requestqueue,
        RENAME INDEX pend_reserved TO request_fk_reserved_user,

        RENAME INDEX acc_pend_status_mailconf TO request_idx_status_emailconfirm,
        RENAME INDEX ft_useragent TO request_idx_useragent,
        RENAME INDEX ip TO request_idx_ip,
        RENAME INDEX mailconfirm TO request_idx_emailconfirm,
        RENAME INDEX pend_email_status TO request_idx_email_emailconfirm,
        RENAME INDEX pend_ip_status TO request_idx_ip_emailconfirm;

    ALTER TABLE request
        ADD CONSTRAINT request_fk_originform_requestform
            FOREIGN KEY (originform) REFERENCES requestform (id),
        ADD CONSTRAINT request_fk_queue_requestqueue
            FOREIGN KEY (queue) REFERENCES requestqueue (id),
        ADD CONSTRAINT request_fk_reserved_user
            FOREIGN KEY (reserved) REFERENCES user (id);

    ALTER TABLE requestdata
        DROP FOREIGN KEY requestdata_request_id_fk;

    ALTER TABLE requestdata
        RENAME INDEX idx_requestdata_type_value TO requestdata_idx_type_value,
        RENAME INDEX requestdata_request_id_fk TO requestdata_fk_request_request;

    ALTER TABLE requestdata
        ADD CONSTRAINT requestdata_fk_request_request
            FOREIGN KEY (request) REFERENCES request (id)
                ON DELETE CASCADE;

    ALTER TABLE requestform
        DROP FOREIGN KEY requestform_domain_id_fk,
        DROP FOREIGN KEY requestform_requestqueue_id_fk,
        DROP CONSTRAINT requestform_enabled;

    ALTER TABLE requestform
        RENAME INDEX requestform_domain_publicendpoint_uindex TO requestform_uidx_domain_publicendpoint,
        RENAME INDEX requestform_name TO requestform_uidx_domain_name,
        ADD INDEX requestform_fk_domain_domain (domain),
        RENAME INDEX requestform_requestqueue_id_fk TO requestform_fk_overridequeue_requestqueue;

    ALTER TABLE requestform
        ADD CONSTRAINT requestform_fk_domain_domain
            FOREIGN KEY (domain) REFERENCES domain (id),
        ADD CONSTRAINT requestform_fk_overridequeue_requestqueue
            FOREIGN KEY (overridequeue) REFERENCES requestqueue (id),
        ADD CONSTRAINT requestform_check_enabled
            CHECK (`enabled` IN (0, 1));

    ALTER TABLE requestqueue
        DROP FOREIGN KEY requestqueue_domain_id_fk,
        DROP CONSTRAINT requestqueue_defaultantispoof,
        DROP CONSTRAINT requestqueue_defaultisenabled,
        DROP CONSTRAINT requestqueue_defaulttitleblacklist,
        DROP CONSTRAINT requestqueue_enabled,
        DROP CONSTRAINT requestqueue_isdefault;

    ALTER TABLE requestqueue
        RENAME INDEX requestqueue_apiname_uniq TO requestqueue_uidx_domain_apiname,
        RENAME INDEX requestqueue_displayname_uniq TO requestqueue_uidx_domain_displayname,
        RENAME INDEX requestqueue_header_uniq TO requestqueue_uidx_domain_header,
        ADD INDEX requestqueue_fk_domain_domain (domain);

    ALTER TABLE requestqueue
        ADD CONSTRAINT requestqueue_fk_domain_domain
            FOREIGN KEY (domain) REFERENCES domain (id),

        ADD CONSTRAINT requestqueue_check_defaultantispoof
            CHECK (`defaultantispoof` IN (0, 1)),
        ADD CONSTRAINT requestqueue_check_defaultisenabled
            CHECK (CASE
                       WHEN `isdefault` = 1 AND `enabled` = 0 THEN 0
                       WHEN `defaulttitleblacklist` = 1 AND `enabled` = 0 THEN 0
                       WHEN `defaultantispoof` = 1 AND `enabled` = 0 THEN 0
                       ELSE 1
                END),
        ADD CONSTRAINT requestqueue_check_defaulttitleblacklist
            CHECK (`defaulttitleblacklist` IN (0, 1)),
        ADD CONSTRAINT requestqueue_check_enabled
            CHECK (`enabled` IN (0, 1)),
        ADD CONSTRAINT requestqueue_check_isdefault
            CHECK (`isdefault` IN (0, 1));

    ALTER TABLE tornodecache
        RENAME INDEX idx_ipaddrexit_uniq TO tornodecache_uidx_ipaddr_exitaddr,
        RENAME INDEX idx_ipaddr TO tornodecache_idx_ipaddr;

    ALTER TABLE user
        DROP CONSTRAINT user_forceidentified,
        DROP CONSTRAINT user_forcelogout;

    ALTER TABLE user
        RENAME INDEX I_username TO user_uidx_username,
        RENAME INDEX user_email_UNIQUE TO user_uidx_email,
        RENAME INDEX idx_user_lastactive TO user_idx_lastactive,
        RENAME INDEX idx_user_status TO user_idx_status;

    ALTER TABLE user
        ADD CONSTRAINT user_check_forceidentified
            CHECK (`forceidentified` IN (0, 1)),
        ADD CONSTRAINT user_check_forcelogout
            CHECK (`forcelogout` IN (0, 1));

    ALTER TABLE userdomain
        DROP FOREIGN KEY userdomain_domain_id_fk,
        DROP FOREIGN KEY userdomain_user_id_fk;

    ALTER TABLE userdomain
        RENAME INDEX userdomain_user_domain_uindex TO userdomain_uidx_user_domain,
        RENAME INDEX userdomain_domain_id_fk TO userdomain_fk_domain_domain,
        ADD INDEX userdomain_fk_user_user (user);

    ALTER TABLE userdomain
        ADD CONSTRAINT userdomain_fk_domain_domain
            FOREIGN KEY (domain) REFERENCES domain (id),
        ADD CONSTRAINT userdomain_fk_user_user
            FOREIGN KEY (user) REFERENCES user (id);

    ALTER TABLE userpreference
        DROP FOREIGN KEY userpreference_domain_id_fk,
        DROP FOREIGN KEY userpreference_user_id_fk;

    ALTER TABLE userpreference
        RENAME INDEX userpreference_userdomain_uniq TO userpreference_uidx_user_preference_domain,
        RENAME INDEX userpreference_userglobal_uniq TO userpreference_uidx_user_preference_global,
        RENAME INDEX userpreference_domain_id_fk TO userpreference_fk_domain_domain,
        ADD INDEX userpreference_fk_user_user (user);

    ALTER TABLE userpreference
        ADD CONSTRAINT userpreference_fk_domain_domain
            FOREIGN KEY (domain) REFERENCES domain (id),
        ADD CONSTRAINT userpreference_fk_user_user
            FOREIGN KEY (user) REFERENCES user (id);

    ALTER TABLE userrole
        DROP FOREIGN KEY userrole_user_id_fk;

    ALTER TABLE userrole
        ADD INDEX userrole_fk_user_user (user);

    ALTER TABLE userrole
        ADD CONSTRAINT userrole_fk_user_user
            FOREIGN KEY (user) REFERENCES user (id);

    ALTER TABLE welcometemplate
        DROP CONSTRAINT welcometemplate_deleted;

    ALTER TABLE welcometemplate
        ADD CONSTRAINT welcometemplate_check_deleted
            CHECK (`deleted` IN (0, 1));

    ALTER TABLE xfftrustcache
        RENAME INDEX IDX_xfftrustcache_ip TO xfftrustcache_idx_ip;

    -- -------------------------------------------------------------------------
    -- finally, update the schema version to indicate success
    # noinspection SqlWithoutWhere
    UPDATE schemaversion SET version = patchversion;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
