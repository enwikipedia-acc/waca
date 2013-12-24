CREATE OR REPLACE VIEW user AS
    SELECT 
        user_id AS 'id',
        user_name AS 'username',
        user_email AS 'email',
        user_pass AS 'password',
        user_level AS 'status',
        user_onwikiname AS 'onwikiname',
        user_welcome_sig AS 'welcome_sig',
        user_lastactive AS 'lastactive',
        user_forcelogout AS 'forcelogout',
        user_secure AS 'secure',
        user_checkuser AS 'checkuser',
        user_identified AS 'identified',
        user_welcome_templateid AS 'welcome_template',
        user_abortpref AS 'abortpref',
        user_confirmationdiff AS 'confirmationdiff',
        user_emailsig AS 'emailsig'
    FROM
        acc_user;