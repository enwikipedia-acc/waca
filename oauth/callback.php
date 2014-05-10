<?php

// OAuth callback script
// THIS IS AN ENTRY POINT
chdir("..");

// stop all output until we want it
ob_start();

// load the configuration
require_once 'config.inc.php';

// Initialize the session data.
session_start();

// Get all the classes.
require_once 'LogClass.php';
require_once 'functions.php';
require_once 'includes/PdoDatabase.php';
require_once 'includes/SmartyInit.php'; // this needs to be high up, but below config, functions, and database
require_once 'includes/database.php';
require_once 'includes/skin.php';
require_once 'includes/accbotSend.php';
require_once 'includes/session.php';
require_once 'lib/mediawiki-extensions-OAuth/lib/OAuth.php';
require_once 'oauth/OAuthUtility.php';

// Initialize the database classes.
$tsSQL = new database("toolserver");
$asSQL = new database("antispoof");

// Creates database links for later use.
$tsSQLlink = $tsSQL->getLink();
$asSQLlink = $asSQL->getLink();

$user = User::getByRequestToken($_GET['oauth_token'], gGetDb());

if($user == false)
{
    BootstrapSkin::displayInternalHeader();
    BootstrapSkin::displayAlertBox("Could not find request token in local store.", "alert-error", "Error", true, false);
    BootstrapSkin::displayInternalFooter();
    die();
}

global $oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal;

$util = new OAuthUtility($oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal);

try
{
    $result = $util->callbackCompleted($user->getOAuthRequestToken(), $user->getOAuthRequestSecret(), $_GET['oauth_verifier']);
}
catch (Exception $exception)
{
    BootstrapSkin::displayInternalHeader();
    BootstrapSkin::displayAlertBox("OAuth Error: {$exception->getMessage()}", "alert-error", "OAuth Error", true, false);
    BootstrapSkin::displayInternalFooter();
    die();
}

$user->setOAuthAccessToken($result->key);
$user->setOAuthAccessSecret($result->secret);
$user->setOnWikiName("##OAUTH##");
$user->save();

if( $user->getStatus() == "New" )
{
    header("Location: ../acc.php?action=registercomplete");
    die();
}

header("Location: ../acc.php?action=prefs");
die();