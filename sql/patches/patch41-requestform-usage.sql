/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/
# noinspection SqlResolveForFile

DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;
DELIMITER ';;'
CREATE PROCEDURE SCHEMA_UPGRADE_SCRIPT() BEGIN
    -- -------------------------------------------------------------------------
    -- Developers - set the number of the schema patch here!
    -- -------------------------------------------------------------------------
    DECLARE patchversion INT DEFAULT 41;
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

    alter table request
        add originform int(10) unsigned null,
        add constraint request_requestform_id_fk
            foreign key (originform) references requestform (id) ;

    alter table requestform
        add usernamehelp text not null,
        add emailhelp text not null,
        add commentshelp text not null;


    INSERT INTO requestform (updateversion, enabled, domain, name, publicendpoint, formcontent, overridequeue, usernamehelp, emailhelp, commentshelp) VALUES (0, 1, 1, 'Default form', 'default', '## Request an account!


We will need a few bits of information in order to create your account. However, please keep in mind that you do not need an account to read the encyclopedia or look up information - that can be done by anyone with or without an account. The first thing we need is a username, and secondly, a **valid email address that we can send your password to** (please don''t use temporary inboxes, or email aliasing, as this may cause your request to be rejected). If you want to leave any comments, feel free to do so in the comments field below. Note that if you use this form, your IP address will be recorded, and displayed to [those who review account requests](https://accounts.wmflabs.org/internal.php/statistics/users). When you are done, click the "Submit" button.

**Please note!**
We do not have access to existing account data. If you have lost your password, please reset it using [this form](https://en.wikipedia.org/wiki/Special:PasswordReset) at wikipedia.org. If you are trying to ''take over'' an account that already exists, please use ["Changing usernames/Usurpation"](http://en.wikipedia.org/wiki/WP:CHU/U) at wikipedia.org. We cannot do either of these things for you.
{:.alert.alert-warning}

If you have not yet done so, please review the [Username Policy](https://en.wikipedia.org/wiki/Wikipedia:Username_policy) before submitting a request.', null, 'Case sensitive, first letter is always capitalized, you do not need to use all uppercase. Note that this need not be your real name. Please make sure you don''t leave any trailing spaces or underscores on your requested username. Usernames may not consist entirely of numbers, contain the following characters: `# / | [ ] { } < > @ % :` or exceed 85 characters in length.', 'We need a valid email in order to send you your password and confirm your account request. Without it, you will not receive your password, and will be unable to log in to your account.', 'Any additional details you feel are relevant may be placed here. **Please do NOT ask for a specific password. One will be randomly created for you.**');
    commit;

    drop index requestform_publicendpoint_uindex on requestform;

    create unique index requestform_domain_publicendpoint_uindex
        on requestform (domain, publicendpoint);


    -- -------------------------------------------------------------------------
    -- finally, update the schema version to indicate success
    UPDATE schemaversion SET version = patchversion;
END;;
DELIMITER ';'
CALL SCHEMA_UPGRADE_SCRIPT();
DROP PROCEDURE IF EXISTS SCHEMA_UPGRADE_SCRIPT;