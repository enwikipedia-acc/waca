DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
DELIMITER ';;'
CREATE PROCEDURE SCHEMA_UPGRADE_SCRIPT() BEGIN
    -- -------------------------------------------------------------------------
    -- Developers - set the number of the schema patch here!
    -- -------------------------------------------------------------------------
    DECLARE patchversion INT DEFAULT 36;
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

    -- ------------------------------------------------------------
    -- temporarily drop foreign key constraints

    alter table ban
        drop constraint ban_user_id_fk;

    alter table comment
        drop constraint comment_request_id_fk,
        drop constraint comment_user_id_fk;

    alter table credential
        drop constraint credential_user_id_fk;

    alter table jobqueue
        drop constraint jobqueue_emailtemplate_id_fk,
        drop constraint jobqueue_parent_id_fk,
        drop constraint jobqueue_request_id_fk,
        drop constraint jobqueue_user_id_fk;

    alter table oauthidentity
        drop constraint oauthidentity_user_id_fk;

    alter table oauthtoken
        drop constraint oauthtoken_user_id_fk;

    alter table user
        drop constraint user_welcometemplate_id_fk;

    alter table userrole
        drop constraint userrole_user_id_fk;

    -- ------------------------------------------------------------
    -- modify column specifications to use unsigned integers / tinyints where appropriate

    alter table antispoofcache
        modify id int(11) unsigned auto_increment,
        modify updateversion int(11) unsigned default 0 not null
    ;

    alter table applicationlog
        modify id int(11) unsigned auto_increment
    ;

    alter table ban
        modify id int(11) unsigned auto_increment,
        modify active tinyint(1) unsigned default 1 not null,
        modify updateversion int(11) unsigned default 0 not null,
        modify user int(11) unsigned not null,
        modify duration int(11) unsigned null
    ;

    alter table comment
        modify id int(11) unsigned auto_increment,
        modify user int(11) unsigned default 0 null,
        modify request int(11) unsigned not null,
        modify updateversion int(11) unsigned default 0 not null
    ;

    alter table credential
        modify id int(11) unsigned auto_increment,
        modify updateversion int(11) unsigned default 0 not null,
        modify user int(11) unsigned not null,
        modify factor int(11) unsigned default 1 not null,
        modify version int(11) unsigned default 1 not null comment 'Version of the credential, used to determine if the storage method needs to be updated',
        modify disabled tinyint(1) unsigned default 0 not null comment 'Flag determining whether this credential is in use. Used during 2FA setup, operationally useful for disabling 2FA for a user.',
        modify priority int(1) unsigned default 5 null comment 'the order in which these credentials should be tested'
    ;

    alter table emailtemplate
        modify id int(11) unsigned auto_increment comment 'Table key',
        modify active tinyint(1) unsigned default 1 not null comment '1 if the template should be an available option to users. Default 1',
        modify preloadonly tinyint(1) unsigned default 0 not null comment '1 if the template is only available as a custom close preload',
        modify updateversion int(11) unsigned default 0 not null
    ;

    alter table geolocation
        modify id int(11) unsigned auto_increment,
        modify updateversion int(11) unsigned default 0 not null
    ;

    alter table jobqueue
        modify id int(11) unsigned auto_increment,
        modify updateversion int(11) unsigned default 0 not null,
        modify user int(11) unsigned null,
        modify request int(11) unsigned null,
        modify emailtemplate int(11) unsigned null,
        modify acknowledged tinyint(1) unsigned null comment 'flag indicating if a job error has been acknowledged',
        modify parent int(11) unsigned null
    ;

    alter table log
        modify id int(11) unsigned auto_increment,
        modify objectid int(11) unsigned not null,
        modify updateversion int(11) unsigned default 0 not null
        -- the user column here is stupid, as it uses -1 for the community user. We should fix this, but not now. #607
    ;

    alter table oauthidentity
        modify id int(11) unsigned auto_increment,
        modify updateversion int(11) unsigned default 0 not null,
        modify user int(11) unsigned not null,
        modify sub int(11) unsigned not null comment 'centralauth user id',
        modify editcount int(11) unsigned not null,
        modify confirmed_email tinyint(1) unsigned not null,
        modify blocked tinyint(1) unsigned not null,
        modify checkuser tinyint(1) unsigned not null,
        modify grantbasic tinyint(1) unsigned not null,
        modify grantcreateaccount tinyint(1) unsigned not null,
        modify granthighvolume tinyint(1) unsigned not null,
        modify grantcreateeditmovepage tinyint(1) unsigned not null
    ;

    alter table oauthtoken
        modify id int(11) unsigned auto_increment,
        modify updateversion int(11) unsigned default 0 not null,
        modify user int(11) unsigned not null
    ;

    alter table rdnscache
        modify id int(11) unsigned auto_increment,
        modify updateversion int(11) unsigned default 0 not null
    ;

    alter table request
        modify id int(11) unsigned auto_increment,
        modify reserved int(11) unsigned null comment 'User ID of user who has "reserved" this request',
        modify updateversion int(11) unsigned default 0 not null
    ;

    alter table schemaversion
        modify version int(11) unsigned default 10 not null
    ;

    alter table sitenotice
        modify id int(11) unsigned auto_increment,
        modify updateversion int(11) unsigned default 0 not null
    ;

    alter table tornodecache
        modify id int(11) unsigned auto_increment,
        modify updateversion int(11) unsigned default 0 not null,
        modify exitport int(11) unsigned not null
    ;

    alter table user
        modify id int(11) unsigned auto_increment,
        modify forcelogout tinyint(1) unsigned default 0 not null comment 'flag indicating all user sessions should be invalidated',
        modify forceidentified tinyint(1) unsigned null comment 'flag forcing the identification status of a user.',
        modify welcome_template int(11) unsigned null,
        modify abortpref tinyint(1) unsigned default 0 not null comment 'flag indicating whether JS notifications are used on request closure',
        modify updateversion int(11) unsigned default 0 not null,
        modify creationmode int(11) unsigned default 0 not null comment 'flag indicating manual/oauth/bot creation'
    ;

    alter table userrole
        modify id int(11) unsigned auto_increment,
        modify user int(11) unsigned not null,
        modify updateversion int(11) unsigned default 0 not null
    ;

    alter table welcometemplate
        modify id int(11) unsigned auto_increment,
        modify updateversion int(11) unsigned default 0 not null,
        modify deleted tinyint(1) unsigned default 0 not null
    ;

    alter table xfftrustcache
        modify id int(11) unsigned auto_increment
    ;

    -- ------------------------------------------------------------
    -- restore dropped constraints

    alter table ban
        add constraint ban_user_id_fk FOREIGN KEY (user) REFERENCES user (id)
    ;

    alter table comment
        add constraint comment_request_id_fk FOREIGN KEY (request) REFERENCES request (id),
        add constraint comment_user_id_fk FOREIGN KEY (user) REFERENCES user (id)
    ;

    alter table credential
        add constraint credential_user_id_fk FOREIGN KEY (user) REFERENCES user (id)
    ;

    alter table jobqueue
        add constraint jobqueue_emailtemplate_id_fk FOREIGN KEY (emailtemplate) REFERENCES emailtemplate (id),
        add constraint jobqueue_parent_id_fk FOREIGN KEY (parent) REFERENCES jobqueue (id),
        add constraint jobqueue_request_id_fk FOREIGN KEY (request) REFERENCES request (id),
        add constraint jobqueue_user_id_fk FOREIGN KEY (user) REFERENCES user (id)
    ;

    alter table oauthidentity
        add constraint oauthidentity_user_id_fk FOREIGN KEY (user) REFERENCES user (id)
    ;

    alter table oauthtoken
        add constraint oauthtoken_user_id_fk foreign key (user) references user (id)
    ;

    alter table user
        add constraint user_welcometemplate_id_fk foreign key (welcome_template) references welcometemplate (id)
    ;

    alter table userrole
        add constraint userrole_user_id_fk foreign key (user) references user (id)
    ;

    -- ------------------------------------------------------------
    -- drop deprecated/unused columns

    # noinspection SqlResolve @ column/"oncreated"
    alter table emailtemplate
        drop column oncreated
    ;

    -- ------------------------------------------------------------
    -- add new missing constraints

    alter table ban
        add constraint ban_active check (active in (0,1))
    ;

    alter table credential
        add constraint credential_disabled check (disabled in (0,1))
    ;

    alter table emailtemplate
        add constraint emailtemplate_active check (active in (0,1))
    ;

    alter table jobqueue
        add constraint jobqueue_acknowledged check (acknowledged in (0,1))
    ;

    alter table oauthidentity
        add constraint oauthidentity_confirmed_email check (confirmed_email in (0,1)),
        add constraint oauthidentity_blocked check (blocked in (0,1)),
        add constraint oauthidentity_checkuser check (checkuser in (0,1)),
        add constraint oauthidentity_grantbasic check (grantbasic in (0,1)),
        add constraint oauthidentity_grantcreateaccount check (grantcreateaccount in (0,1)),
        add constraint oauthidentity_granthighvolume check (granthighvolume in (0,1)),
        add constraint oauthidentity_grantcreateeditmovepage check (grantcreateeditmovepage in (0,1))
    ;

    alter table request
        add constraint request_user_id_fk foreign key (reserved) references user (id)
    ;

    alter table user
        add constraint user_forcelogout check (forcelogout in (0,1)),
        add constraint user_forceidentified check (forceidentified in (0,1)),
        add constraint user_abortpref check (abortpref in (0,1)),
        add constraint user_creationmode check (creationmode in (0,1,2))
    ;

    alter table welcometemplate
        add constraint welcometemplate_deleted check (deleted in (0,1))
    ;

    -- ------------------------------------------------------------
    -- add tables for multiproject work

    create table domain
    (
        id int unsigned auto_increment primary key,
        updateversion int unsigned default 0 not null,
        shortname varchar(20) not null comment 'short identifier such as database name, eg enwiki or bnwiki',
        longname varchar(255) not null comment 'long name of the wiki, such as English Wikipedia',
        wikiarticlepath varchar(255) not null comment 'https://en.wikipedia.org/wiki/',
        wikiapipath varchar(255) not null comment 'https://en.wikipedia.org/w/api.php',
        enabled tinyint unsigned default 0 not null,
        defaultclose int unsigned null comment 'the default "created" close template to use',
        defaultlanguage varchar(10) not null default 'en' comment 'the iso language code for users created in this domain',
        emailsender varchar(255) not null comment 'the from/sender address of the mailing list, for users to reply with',
        notificationtarget varchar(255) null comment 'the helpmebot target of the notifications from this domain',
        constraint domain_emailtemplate_id_fk foreign key (defaultclose) references emailtemplate (id),
        constraint domain_wikiarticlepath_uniq unique (wikiarticlepath),
        constraint domain_wikiapipath_uniq unique (wikiapipath),
        constraint domain_enabled check (enabled in (0,1))
    ) engine=InnoDB;

    create unique index domain_shortname_uindex on domain (shortname);

    create table userdomain
    (
        id int unsigned auto_increment primary key,
        updateversion int unsigned default 0 not null,
        user int unsigned not null,
        domain int unsigned not null,
        constraint userdomain_domain_id_fk foreign key (domain) references domain (id),
        constraint userdomain_user_id_fk foreign key (user) references user (id)
    ) engine=InnoDB;

    create unique index userdomain_user_domain_uindex on userdomain (user, domain);

    create table requestqueue
    (
        id int unsigned auto_increment primary key,
        updateversion int unsigned default 0 not null,
        enabled tinyint unsigned default 0 not null,
        isdefault tinyint unsigned default 0 not null comment 'whether this is the default queue for the domain. only one set per domain',
        domain int unsigned not null,
        apiname varchar(20) not null comment 'the name used in the api and for JS calls',
        displayname varchar(100) not null comment 'the display name in the GUI',
        header varchar(100) not null comment 'accordion/group name',
        help text null comment 'help text to be displayed under the queue',
        logname varchar(50) not null comment 'name used in logs, to be removed in issue #607',
        legacystatus varchar(40) not null comment 'old status value from legacy request table, to be removed in issue #602',
        constraint requestqueue_header_uniq unique (domain, header),
        constraint requestqueue_apiname_uniq unique (domain, apiname),
        constraint requestqueue_displayname_uniq unique (domain, displayname),
        constraint requestqueue_domain_id_fk foreign key (domain) references domain (id),
        constraint requestqueue_isdefault check (isdefault in (0,1)),
        constraint requestqueue_enabled check (enabled in (0,1)),
        constraint requestqueue_defaultisenabled check (case when isdefault = 1 and enabled = 0 then false else true end)
    ) engine=InnoDB;

    create table requestform
    (
        id int unsigned auto_increment primary key ,
        updateversion int unsigned default 0 not null,
        enabled tinyint unsigned default 0 not null,
        domain int unsigned not null,
        name varchar(255) not null comment 'display name of the form for identification in the UI',
        publicendpoint varchar(64) not null comment 'public URL segment of the form',
        formcontent longtext not null comment 'text content of the form',
        overridequeue int unsigned null comment 'override the default queue to send this request to',
        constraint requestform_domain_id_fk foreign key (domain) references domain (id),
        constraint requestform_requestqueue_id_fk foreign key (overridequeue) references requestqueue (id),
        constraint requestform_enabled check (enabled in (0,1)),
        constraint requestform_name unique (domain, name)
    ) engine=InnoDB;

    create unique index requestform_publicendpoint_uindex on requestform (publicendpoint);

    -- -------------------------------------------------------------------------
    -- finally, update the schema version to indicate success
    # noinspection SqlWithoutWhere
    UPDATE schemaversion SET version = patchversion;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;