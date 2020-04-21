DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
DELIMITER ';;'
CREATE PROCEDURE SCHEMA_UPGRADE_SCRIPT() BEGIN
    -- -------------------------------------------------------------------------
    -- Developers - set the number of the schema patch here!
    -- -------------------------------------------------------------------------
    DECLARE patchversion INT DEFAULT 18;
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

    ALTER TABLE antispoofcache ADD username_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER username, ROW_FORMAT=DYNAMIC;
    ALTER TABLE applicationlog ADD source_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER source, ROW_FORMAT=DYNAMIC;
    ALTER TABLE applicationlog ADD request_utf8 varchar(1024) null collate utf8mb4_unicode_520_ci AFTER request;
    ALTER TABLE ban ADD type_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER type, ROW_FORMAT=DYNAMIC;
    ALTER TABLE ban ADD target_utf8 varchar(700) null collate utf8mb4_unicode_520_ci AFTER target;
    ALTER TABLE ban ADD user_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER user;
    ALTER TABLE ban ADD reason_utf8 varchar(4096) null collate utf8mb4_unicode_520_ci AFTER reason;
    ALTER TABLE ban ADD date_utf8 varchar(1024) null collate utf8mb4_unicode_520_ci AFTER date;
    ALTER TABLE ban ADD duration_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER duration, ROW_FORMAT=DYNAMIC;
    ALTER TABLE comment ADD visibility_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER visibility, ROW_FORMAT=DYNAMIC;
    ALTER TABLE emailtemplate ADD name_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER name, ROW_FORMAT=DYNAMIC;
    ALTER TABLE emailtemplate ADD defaultaction_utf8 varchar(45) null collate utf8mb4_unicode_520_ci AFTER defaultaction;
    ALTER TABLE geolocation ADD address_utf8 varchar(45) null collate utf8mb4_unicode_520_ci AFTER address, ROW_FORMAT=DYNAMIC;
    ALTER TABLE id ADD enwikiname_utf8 varchar(50) null collate utf8mb4_unicode_520_ci AFTER enwikiname, ROW_FORMAT=DYNAMIC;
    ALTER TABLE interfacemessage ADD description_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER description, ROW_FORMAT=DYNAMIC;
    ALTER TABLE interfacemessage ADD type_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER type;
    ALTER TABLE log ADD objecttype_utf8 varchar(45) null collate utf8mb4_unicode_520_ci AFTER objecttype, ROW_FORMAT=DYNAMIC;
    ALTER TABLE log ADD action_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER action;
    ALTER TABLE rdnscache ADD address_utf8 varchar(45) null collate utf8mb4_unicode_520_ci AFTER address, ROW_FORMAT=DYNAMIC;
    ALTER TABLE request ADD email_utf8 varchar(512) null collate utf8mb4_unicode_520_ci AFTER email, ROW_FORMAT=DYNAMIC;
    ALTER TABLE request ADD ip_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER ip, ROW_FORMAT=DYNAMIC;
    ALTER TABLE request ADD name_utf8 varchar(512) null collate utf8mb4_unicode_520_ci AFTER name;
    ALTER TABLE request ADD comment_utf8 varchar(3000) null collate utf8mb4_unicode_520_ci AFTER comment;
    ALTER TABLE request ADD status_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER status;
    ALTER TABLE request ADD checksum_utf8 varchar(256) null collate utf8mb4_unicode_520_ci AFTER checksum;
    ALTER TABLE request ADD emailsent_utf8 varchar(10) null collate utf8mb4_unicode_520_ci AFTER emailsent;
    ALTER TABLE request ADD emailconfirm_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER emailconfirm;
    ALTER TABLE request ADD forwardedip_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER forwardedip;
    ALTER TABLE user ADD username_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER username, ROW_FORMAT=DYNAMIC;
    ALTER TABLE user ADD email_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER email;
    ALTER TABLE user ADD password_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER password;
    ALTER TABLE user ADD status_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER status;
    ALTER TABLE user ADD onwikiname_utf8 varchar(255) null collate utf8mb4_unicode_520_ci AFTER onwikiname;
    ALTER TABLE user ADD welcome_sig_utf8 varchar(4096) null collate utf8mb4_unicode_520_ci AFTER welcome_sig;
    ALTER TABLE user ADD oauthrequesttoken_utf8 varchar(45) null collate utf8mb4_unicode_520_ci AFTER oauthrequesttoken;
    ALTER TABLE user ADD oauthrequestsecret_utf8 varchar(45) null collate utf8mb4_unicode_520_ci AFTER oauthrequestsecret;
    ALTER TABLE user ADD oauthaccesstoken_utf8 varchar(45) null collate utf8mb4_unicode_520_ci AFTER oauthaccesstoken;
    ALTER TABLE user ADD oauthaccesssecret_utf8 varchar(45) null collate utf8mb4_unicode_520_ci AFTER oauthaccesssecret;
    ALTER TABLE xfftrustcache ADD ip_utf8 varchar(15) null collate utf8mb4_unicode_520_ci AFTER ip, ROW_FORMAT=DYNAMIC;

    ALTER TABLE applicationlog ADD message_utf8 longtext null collate utf8mb4_unicode_520_ci AFTER message;
    ALTER TABLE applicationlog ADD stack_utf8 longtext null collate utf8mb4_unicode_520_ci AFTER stack;
    ALTER TABLE comment ADD comment_utf8 mediumtext null collate utf8mb4_unicode_520_ci AFTER comment;
    ALTER TABLE emailtemplate ADD jsquestion_utf8 longtext null collate utf8mb4_unicode_520_ci AFTER jsquestion;
    ALTER TABLE welcometemplate ADD usercode_utf8 text null collate utf8mb4_unicode_520_ci AFTER usercode;
    ALTER TABLE welcometemplate ADD botcode_utf8 text null collate utf8mb4_unicode_520_ci AFTER botcode;

    ALTER TABLE antispoofcache ADD data_utf8 text null collate utf8mb4_unicode_520_ci AFTER data;
    ALTER TABLE emailtemplate ADD text_utf8 text null collate utf8mb4_unicode_520_ci AFTER text;
    ALTER TABLE geolocation ADD data_utf8 text null collate utf8mb4_unicode_520_ci AFTER data;
    ALTER TABLE interfacemessage ADD content_utf8 text null collate utf8mb4_unicode_520_ci AFTER content;
    ALTER TABLE log ADD comment_utf8 text null collate utf8mb4_unicode_520_ci AFTER comment;
    ALTER TABLE rdnscache ADD data_utf8 text null collate utf8mb4_unicode_520_ci AFTER data;
    ALTER TABLE request ADD useragent_utf8 text null collate utf8mb4_unicode_520_ci AFTER useragent;
    ALTER TABLE user ADD emailsig_utf8 text null collate utf8mb4_unicode_520_ci AFTER emailsig;
    ALTER TABLE user ADD oauthidentitycache_utf8 text null collate utf8mb4_unicode_520_ci AFTER oauthidentitycache;



    UPDATE antispoofcache SET username_utf8 = convert(cast(convert(username using latin1) as binary) using utf8mb4) where username is not null and username_utf8 is null;
    UPDATE applicationlog SET source_utf8 = convert(cast(convert(source using latin1) as binary) using utf8mb4) where source is not null and source_utf8 is null;
    UPDATE applicationlog SET request_utf8 = convert(cast(convert(request using latin1) as binary) using utf8mb4) where request is not null and request_utf8 is null;
    UPDATE ban SET type_utf8 = convert(cast(convert(type using latin1) as binary) using utf8mb4) where type is not null and type_utf8 is null;
    UPDATE ban SET target_utf8 = convert(cast(convert(target using latin1) as binary) using utf8mb4) where target is not null and target_utf8 is null;
    UPDATE ban SET user_utf8 = convert(cast(convert(user using latin1) as binary) using utf8mb4) where user is not null and user_utf8 is null;
    UPDATE ban SET reason_utf8 = convert(cast(convert(reason using latin1) as binary) using utf8mb4) where reason is not null and reason_utf8 is null;
    UPDATE ban SET date_utf8 = convert(cast(convert(date using latin1) as binary) using utf8mb4) where date is not null and date_utf8 is null;
    UPDATE ban SET duration_utf8 = convert(cast(convert(duration using latin1) as binary) using utf8mb4) where duration is not null and duration_utf8 is null;
    UPDATE comment SET visibility_utf8 = convert(cast(convert(visibility using latin1) as binary) using utf8mb4) where visibility is not null and visibility_utf8 is null;
    UPDATE emailtemplate SET name_utf8 = convert(cast(convert(name using latin1) as binary) using utf8mb4) where name is not null and name_utf8 is null;
    UPDATE emailtemplate SET defaultaction_utf8 = convert(cast(convert(defaultaction using latin1) as binary) using utf8mb4) where defaultaction is not null and defaultaction_utf8 is null;
    UPDATE geolocation SET address_utf8 = convert(cast(convert(address using latin1) as binary) using utf8mb4) where address is not null and address_utf8 is null;
    UPDATE id SET enwikiname_utf8 = convert(cast(convert(enwikiname using latin1) as binary) using utf8mb4) where enwikiname is not null and enwikiname_utf8 is null;
    UPDATE id SET enwikiname_utf8 = enwikiname WHERE enwikiname_utf8 <> enwikiname;
    UPDATE interfacemessage SET description_utf8 = convert(cast(convert(description using latin1) as binary) using utf8mb4) where description is not null and description_utf8 is null;
    UPDATE interfacemessage SET type_utf8 = convert(cast(convert(type using latin1) as binary) using utf8mb4) where type is not null and type_utf8 is null;
    UPDATE log SET objecttype_utf8 = convert(cast(convert(objecttype using latin1) as binary) using utf8mb4) where objecttype is not null and objecttype_utf8 is null;
    UPDATE log SET action_utf8 = convert(cast(convert(action using latin1) as binary) using utf8mb4) where action is not null and action_utf8 is null;
    UPDATE rdnscache SET address_utf8 = convert(cast(convert(address using latin1) as binary) using utf8mb4) where address is not null and address_utf8 is null;
    UPDATE request SET email_utf8 = convert(cast(convert(email using latin1) as binary) using utf8mb4) where email is not null and email_utf8 is null;
    UPDATE request SET ip_utf8 = convert(cast(convert(ip using latin1) as binary) using utf8mb4) where ip is not null and ip_utf8 is null;
    UPDATE request SET name_utf8 = convert(cast(convert(name using latin1) as binary) using utf8mb4) where name is not null and name_utf8 is null and id not between 285552 and 285760 and id not in (5045, 38819, 39451, 42083,46299, 47235, 49256, 45502, 50197, 50635);
    UPDATE request SET name_utf8 = name where name is not null and name_utf8 is null and (id between 285552 and 285760 OR id in (5045, 38819, 39451, 42083,46299, 47235, 49256, 45502, 50197, 50635));
    UPDATE request SET comment_utf8 = convert(cast(convert(comment using latin1) as binary) using utf8mb4) where comment is not null and comment_utf8 is null and id not between 285552 and 285760;
    UPDATE request SET comment_utf8 = comment where comment is not null and comment_utf8 is null and id between 285552 and 285760;

    UPDATE request SET status_utf8 = convert(cast(convert(status using latin1) as binary) using utf8mb4) where status is not null and status_utf8 is null;
    UPDATE request SET checksum_utf8 = convert(cast(convert(checksum using latin1) as binary) using utf8mb4) where checksum is not null and checksum_utf8 is null;
    UPDATE request SET emailsent_utf8 = convert(cast(convert(emailsent using latin1) as binary) using utf8mb4) where emailsent is not null and emailsent_utf8 is null;
    UPDATE request SET emailconfirm_utf8 = convert(cast(convert(emailconfirm using latin1) as binary) using utf8mb4) where emailconfirm is not null and emailconfirm_utf8 is null;
    UPDATE request SET forwardedip_utf8 = convert(cast(convert(forwardedip using latin1) as binary) using utf8mb4) where forwardedip is not null and forwardedip_utf8 is null;
    UPDATE user SET username_utf8 = convert(cast(convert(username using latin1) as binary) using utf8mb4) where username is not null and username_utf8 is null and id <> 2038;
    UPDATE user SET username_utf8 = username where username is not null and username_utf8 is null and id = 2038;
    UPDATE user SET email_utf8 = convert(cast(convert(email using latin1) as binary) using utf8mb4) where email is not null and email_utf8 is null;
    UPDATE user SET password_utf8 = convert(cast(convert(password using latin1) as binary) using utf8mb4) where password is not null and password_utf8 is null;
    UPDATE user SET status_utf8 = convert(cast(convert(status using latin1) as binary) using utf8mb4) where status is not null and status_utf8 is null;
    UPDATE user SET onwikiname_utf8 = convert(cast(convert(onwikiname using latin1) as binary) using utf8mb4) where onwikiname is not null and onwikiname_utf8 is null;
    UPDATE user SET welcome_sig_utf8 = convert(cast(convert(welcome_sig using latin1) as binary) using utf8mb4) where welcome_sig is not null and welcome_sig_utf8 is null;
    UPDATE user SET oauthrequesttoken_utf8 = convert(cast(convert(oauthrequesttoken using latin1) as binary) using utf8mb4) where oauthrequesttoken is not null and oauthrequesttoken_utf8 is null;
    UPDATE user SET oauthrequestsecret_utf8 = convert(cast(convert(oauthrequestsecret using latin1) as binary) using utf8mb4) where oauthrequestsecret is not null and oauthrequestsecret_utf8 is null;
    UPDATE user SET oauthaccesstoken_utf8 = convert(cast(convert(oauthaccesstoken using latin1) as binary) using utf8mb4) where oauthaccesstoken is not null and oauthaccesstoken_utf8 is null;
    UPDATE user SET oauthaccesssecret_utf8 = convert(cast(convert(oauthaccesssecret using latin1) as binary) using utf8mb4) where oauthaccesssecret is not null and oauthaccesssecret_utf8 is null;
    UPDATE xfftrustcache SET ip_utf8 = convert(cast(convert(ip using latin1) as binary) using utf8mb4) where ip is not null and ip_utf8 is null;

    UPDATE applicationlog SET message_utf8 = convert(cast(convert(message using latin1) as binary) using utf8mb4) where message is not null and message_utf8 is null;
    UPDATE applicationlog SET stack_utf8 = convert(cast(convert(stack using latin1) as binary) using utf8mb4) where stack is not null and stack_utf8 is null;
    UPDATE comment SET comment_utf8 = convert(cast(convert(comment using latin1) as binary) using utf8mb4) where comment is not null and comment_utf8 is null;
    UPDATE emailtemplate SET jsquestion_utf8 = convert(cast(convert(jsquestion using latin1) as binary) using utf8mb4) where jsquestion is not null and jsquestion_utf8 is null;
    UPDATE welcometemplate SET usercode_utf8 = convert(cast(convert(usercode using latin1) as binary) using utf8mb4) where usercode is not null and usercode_utf8 is null;
    UPDATE welcometemplate SET botcode_utf8 = convert(cast(convert(botcode using latin1) as binary) using utf8mb4) where botcode is not null and botcode_utf8 is null;

    update user set welcome_sig = '', welcome_template = null where id in (28,48,201,215,218,226,262,292,304,307,309,330,336,346,366,382,446,452,487,567,582,611,613,621,626,661,672,695,697,704,726,748);

    UPDATE antispoofcache SET data_utf8 = convert(cast(convert(data using latin1) as binary) using utf8mb4) where data is not null and data_utf8 is null;
    UPDATE emailtemplate SET text_utf8 = convert(cast(convert(text using latin1) as binary) using utf8mb4) where text is not null and text_utf8 is null;
    UPDATE geolocation SET data_utf8 = convert(cast(convert(data using latin1) as binary) using utf8mb4) where data is not null and data_utf8 is null;
    UPDATE interfacemessage SET content_utf8 = convert(cast(convert(content using latin1) as binary) using utf8mb4) where content is not null and content_utf8 is null;
    UPDATE log SET comment_utf8 = convert(cast(convert(comment using latin1) as binary) using utf8mb4) where comment is not null and comment_utf8 is null;
    UPDATE rdnscache SET data_utf8 = convert(cast(convert(data using latin1) as binary) using utf8mb4) where data is not null and data_utf8 is null;
    UPDATE request SET useragent_utf8 = convert(cast(convert(useragent using latin1) as binary) using utf8mb4) where useragent is not null and useragent_utf8 is null;
    UPDATE user SET emailsig_utf8 = convert(cast(convert(emailsig using latin1) as binary) using utf8mb4) where emailsig is not null and emailsig_utf8 is null;
    UPDATE user SET oauthidentitycache_utf8 = convert(cast(convert(oauthidentitycache using latin1) as binary) using utf8mb4) where oauthidentitycache is not null and oauthidentitycache_utf8 is null;



    ALTER TABLE antispoofcache COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE applicationlog COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE ban COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE comment COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE emailtemplate COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE geolocation COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE id COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE interfacemessage COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE log COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE rdnscache COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE request COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE schemaversion COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE user COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE welcometemplate COLLATE utf8mb4_unicode_520_ci;
    ALTER TABLE xfftrustcache COLLATE utf8mb4_unicode_520_ci;


    ALTER TABLE antispoofcache MODIFY COLUMN username varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE applicationlog MODIFY COLUMN source varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE applicationlog MODIFY COLUMN request varchar(1024) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;
    ALTER TABLE ban MODIFY COLUMN type varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE ban MODIFY COLUMN target varchar(150) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE ban MODIFY COLUMN user varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE ban MODIFY COLUMN reason varchar(4096) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE ban MODIFY COLUMN date varchar(1024) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE ban MODIFY COLUMN duration varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE comment MODIFY COLUMN visibility varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE emailtemplate MODIFY COLUMN name varchar(150) COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT 'Name of the Email template';
    ALTER TABLE emailtemplate MODIFY COLUMN defaultaction varchar(45) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL COMMENT 'The default action to take when this template is used for custom closes';
    ALTER TABLE geolocation MODIFY COLUMN address varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE interfacemessage MODIFY COLUMN description varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE interfacemessage MODIFY COLUMN type varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE log MODIFY COLUMN objecttype varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE log MODIFY COLUMN action varchar(40) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE rdnscache MODIFY COLUMN address varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE request MODIFY COLUMN email varchar(190) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE request MODIFY COLUMN ip varchar(40) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE request MODIFY COLUMN name varchar(512) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE request MODIFY COLUMN comment varchar(3000) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE request MODIFY COLUMN status varchar(40) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE request MODIFY COLUMN checksum varchar(256) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE request MODIFY COLUMN emailsent varchar(10) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE request MODIFY COLUMN emailconfirm varchar(65) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE request MODIFY COLUMN forwardedip varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;
    ALTER TABLE user MODIFY COLUMN username varchar(150) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE user MODIFY COLUMN email varchar(150) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE user MODIFY COLUMN password varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE user MODIFY COLUMN status varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'New';
    ALTER TABLE user MODIFY COLUMN onwikiname varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;
    ALTER TABLE user MODIFY COLUMN welcome_sig varchar(4096) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
    ALTER TABLE user MODIFY COLUMN oauthrequesttoken varchar(45) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;
    ALTER TABLE user MODIFY COLUMN oauthrequestsecret varchar(45) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;
    ALTER TABLE user MODIFY COLUMN oauthaccesstoken varchar(45) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;
    ALTER TABLE user MODIFY COLUMN oauthaccesssecret varchar(45) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;
    ALTER TABLE xfftrustcache MODIFY COLUMN ip varchar(15) COLLATE utf8mb4_unicode_520_ci NOT NULL;

    ALTER TABLE applicationlog MODIFY COLUMN message longtext COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE applicationlog MODIFY COLUMN stack longtext COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE comment MODIFY COLUMN comment mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE emailtemplate MODIFY COLUMN jsquestion longtext COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT 'Question in Javascript popup presented to the user when they attempt to use this template';
    ALTER TABLE welcometemplate MODIFY COLUMN usercode text COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE welcometemplate MODIFY COLUMN botcode text COLLATE utf8mb4_unicode_520_ci NOT NULL;

    ALTER TABLE antispoofcache MODIFY COLUMN data TEXT COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE emailtemplate MODIFY COLUMN text TEXT COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT 'Text of the Email template';
    ALTER TABLE geolocation MODIFY COLUMN data TEXT COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE interfacemessage MODIFY COLUMN content TEXT COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE log MODIFY COLUMN comment TEXT COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;
    ALTER TABLE rdnscache MODIFY COLUMN data TEXT COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE request MODIFY COLUMN useragent TEXT COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT 'Useragent of the requesting web browser';
    ALTER TABLE user MODIFY COLUMN emailsig TEXT COLLATE utf8mb4_unicode_520_ci NOT NULL;
    ALTER TABLE user MODIFY COLUMN oauthidentitycache TEXT COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;




    UPDATE antispoofcache SET username = username_utf8 WHERE 1=1;
    UPDATE applicationlog SET source = source_utf8 WHERE 1=1;
    UPDATE applicationlog SET request = request_utf8 WHERE 1=1;
    UPDATE ban SET type = type_utf8 WHERE 1=1;
    UPDATE ban SET target = target_utf8 WHERE 1=1;
    UPDATE ban SET user = user_utf8 WHERE 1=1;
    UPDATE ban SET reason = reason_utf8 WHERE 1=1;
    UPDATE ban SET date = date_utf8 WHERE 1=1;
    UPDATE ban SET duration = duration_utf8 WHERE 1=1;
    UPDATE comment SET visibility = visibility_utf8 WHERE 1=1;
    UPDATE emailtemplate SET name = name_utf8 WHERE 1=1;
    UPDATE emailtemplate SET defaultaction = defaultaction_utf8 WHERE 1=1;
    UPDATE geolocation SET address = address_utf8 WHERE 1=1;
    UPDATE id SET enwikiname = enwikiname_utf8 WHERE 1=1;
    UPDATE interfacemessage SET description = description_utf8 WHERE 1=1;
    UPDATE interfacemessage SET type = type_utf8 WHERE 1=1;
    UPDATE log SET objecttype = objecttype_utf8 WHERE 1=1;
    UPDATE log SET action = action_utf8 WHERE 1=1;
    UPDATE rdnscache SET address = address_utf8 WHERE 1=1;
    UPDATE request SET email = email_utf8 WHERE 1=1;
    UPDATE request SET ip = ip_utf8 WHERE 1=1;
    UPDATE request SET name = name_utf8 WHERE 1=1;
    UPDATE request SET comment = comment_utf8 WHERE 1=1;
    UPDATE request SET status = status_utf8 WHERE 1=1;
    UPDATE request SET checksum = checksum_utf8 WHERE 1=1;
    UPDATE request SET emailsent = emailsent_utf8 WHERE 1=1;
    UPDATE request SET emailconfirm = emailconfirm_utf8 WHERE 1=1;
    UPDATE request SET forwardedip = forwardedip_utf8 WHERE 1=1;
    UPDATE user SET username = username_utf8 WHERE 1=1;
    UPDATE user SET email = email_utf8 WHERE 1=1;
    UPDATE user SET password = password_utf8 WHERE 1=1;
    UPDATE user SET status = status_utf8 WHERE 1=1;
    UPDATE user SET onwikiname = onwikiname_utf8 WHERE 1=1;
    UPDATE user SET welcome_sig = welcome_sig_utf8 WHERE 1=1;
    UPDATE user SET oauthrequesttoken = oauthrequesttoken_utf8 WHERE 1=1;
    UPDATE user SET oauthrequestsecret = oauthrequestsecret_utf8 WHERE 1=1;
    UPDATE user SET oauthaccesstoken = oauthaccesstoken_utf8 WHERE 1=1;
    UPDATE user SET oauthaccesssecret = oauthaccesssecret_utf8 WHERE 1=1;
    UPDATE xfftrustcache SET ip = ip_utf8 WHERE 1=1;

    UPDATE applicationlog SET message = message_utf8 WHERE 1=1;
    UPDATE applicationlog SET stack = stack_utf8 WHERE 1=1;
    UPDATE comment SET comment = comment_utf8 WHERE 1=1;
    UPDATE emailtemplate SET jsquestion = jsquestion_utf8 WHERE 1=1;
    UPDATE welcometemplate SET usercode = usercode_utf8 WHERE 1=1;
    UPDATE welcometemplate SET botcode = botcode_utf8 WHERE 1=1;

    UPDATE antispoofcache SET data = data_utf8 WHERE 1=1;
    UPDATE emailtemplate SET text = text_utf8 WHERE 1=1;
    UPDATE geolocation SET data = data_utf8 WHERE 1=1;
    UPDATE interfacemessage SET content = content_utf8 WHERE 1=1;
    UPDATE log SET comment = comment_utf8 WHERE 1=1;
    UPDATE rdnscache SET data = data_utf8 WHERE 1=1;
    UPDATE request SET useragent = useragent_utf8 WHERE 1=1;
    UPDATE user SET emailsig = emailsig_utf8 WHERE 1=1;
    UPDATE user SET oauthidentitycache = oauthidentitycache_utf8 WHERE 1=1;



    ALTER TABLE antispoofcache DROP COLUMN username_utf8;
    ALTER TABLE applicationlog DROP COLUMN source_utf8;
    ALTER TABLE applicationlog DROP COLUMN request_utf8;
    ALTER TABLE ban DROP COLUMN type_utf8;
    ALTER TABLE ban DROP COLUMN target_utf8;
    ALTER TABLE ban DROP COLUMN user_utf8;
    ALTER TABLE ban DROP COLUMN reason_utf8;
    ALTER TABLE ban DROP COLUMN date_utf8;
    ALTER TABLE ban DROP COLUMN duration_utf8;
    ALTER TABLE comment DROP COLUMN visibility_utf8;
    ALTER TABLE emailtemplate DROP COLUMN name_utf8;
    ALTER TABLE emailtemplate DROP COLUMN defaultaction_utf8;
    ALTER TABLE geolocation DROP COLUMN address_utf8;
    ALTER TABLE id DROP COLUMN enwikiname_utf8;
    ALTER TABLE interfacemessage DROP COLUMN description_utf8;
    ALTER TABLE interfacemessage DROP COLUMN type_utf8;
    ALTER TABLE log DROP COLUMN objecttype_utf8;
    ALTER TABLE log DROP COLUMN action_utf8;
    ALTER TABLE rdnscache DROP COLUMN address_utf8;
    ALTER TABLE request DROP COLUMN email_utf8;
    ALTER TABLE request DROP COLUMN ip_utf8;
    ALTER TABLE request DROP COLUMN name_utf8;
    ALTER TABLE request DROP COLUMN comment_utf8;
    ALTER TABLE request DROP COLUMN status_utf8;
    ALTER TABLE request DROP COLUMN checksum_utf8;
    ALTER TABLE request DROP COLUMN emailsent_utf8;
    ALTER TABLE request DROP COLUMN emailconfirm_utf8;
    ALTER TABLE request DROP COLUMN forwardedip_utf8;
    ALTER TABLE user DROP COLUMN username_utf8;
    ALTER TABLE user DROP COLUMN email_utf8;
    ALTER TABLE user DROP COLUMN password_utf8;
    ALTER TABLE user DROP COLUMN status_utf8;
    ALTER TABLE user DROP COLUMN onwikiname_utf8;
    ALTER TABLE user DROP COLUMN welcome_sig_utf8;
    ALTER TABLE user DROP COLUMN oauthrequesttoken_utf8;
    ALTER TABLE user DROP COLUMN oauthrequestsecret_utf8;
    ALTER TABLE user DROP COLUMN oauthaccesstoken_utf8;
    ALTER TABLE user DROP COLUMN oauthaccesssecret_utf8;
    ALTER TABLE xfftrustcache DROP COLUMN ip_utf8;

    ALTER TABLE applicationlog DROP COLUMN message_utf8;
    ALTER TABLE applicationlog DROP COLUMN stack_utf8;
    ALTER TABLE comment DROP COLUMN comment_utf8;
    ALTER TABLE emailtemplate DROP COLUMN jsquestion_utf8;
    ALTER TABLE welcometemplate DROP COLUMN usercode_utf8;
    ALTER TABLE welcometemplate DROP COLUMN botcode_utf8;

    ALTER TABLE antispoofcache DROP COLUMN data_utf8;
    ALTER TABLE emailtemplate DROP COLUMN text_utf8;
    ALTER TABLE geolocation DROP COLUMN data_utf8;
    ALTER TABLE interfacemessage DROP COLUMN content_utf8;
    ALTER TABLE log DROP COLUMN comment_utf8;
    ALTER TABLE rdnscache DROP COLUMN data_utf8;
    ALTER TABLE request DROP COLUMN useragent_utf8;
    ALTER TABLE user DROP COLUMN emailsig_utf8;
    ALTER TABLE user DROP COLUMN oauthidentitycache_utf8;

    -- -------------------------------------------------------------------------
    -- finally, update the schema version to indicate success
    UPDATE schemaversion SET version = patchversion;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
