<?php
if (isset($_SERVER['REQUEST_METHOD'])) {
	die();
} // Web clients die.

ini_set('display_errors', 1);
ini_set('memory_limit', '256M');

require_once 'config.inc.php';
require_once 'functions.php';
require_once 'includes/PdoDatabase.php';

$database = gGetDb();

$database->transactionally(function() use ($database)
{
    $database->exec(<<<SQL
        UPDATE user 
        SET 
            oauthrequesttoken = null, 
            oauthrequestsecret = null, 
            oauthaccesstoken = null, 
            oauthaccesssecret = null, 
            oauthidentitycache = null;
SQL
    );
});
