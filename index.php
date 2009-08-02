<?php

/**************************************************************
** English Wikipedia Account Request Interface               **
** Wikipedia Account Request Graphic Design by               **
** Charles Melbye is licensed under a Creative               **
** Commons Attribution-Noncommercial-Share Alike             **
** 3.0 United States License. All other code                 **
** released under Public Domain by the ACC                   **
** Development Team.                                         **
**             Developers:                                   **
**  SQL ( http://en.wikipedia.org/User:SQL )                 **
**  Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
**FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
**Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
**Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
**Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
**OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
**Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
**Chris G ( http://en.wikipedia.org/wiki/User:Chris_G )      **
**                                                           **
**************************************************************/

require_once 'config.inc.php';
require_once 'AntiSpoof.php';

$fail = 0;

// get all the classes
require_once 'includes/offlineMessage.php';
require_once 'includes/database.php';
require_once 'includes/request.php';
require_once 'includes/skin.php';
require_once 'includes/messages.php';
require_once 'includes/accbotSend.php';

// check to see if the database is unavailable
$offlineMessage = new offlineMessage(true);
$offlineMessage->check();

// connect to the TS database and the antispoof database
global $toolserver_username, $toolserver_password, $toolserver_host, $toolserver_database;
$tsSQL = new database($toolserver_host,$toolserver_username,$toolserver_password);
$tsSQL->selectDb($toolserver_database);

global $dontUseWikiDb;
if($dontUseWikiDb == 0)
{
	global $antispoof_host, $antispoof_db, $antispoof_table, $antispoof_password;
	$asSQL = new database($antispoof_host,$toolserver_username,$antispoof_password);
	$asSQL->selectDb($antispoof_db); 
}

$request  = new accRequest();
$messages = new messages();
$accbot   = new accbotSend();
$skin     = new skin();

$skin->displayheader();

$action = '';
if( isset( $_GET['action'] ) ) {
	$action = $_GET['action'];
}
if( isset( $_GET['id'] ) ) {
	$request->setID($_GET['id']);
}

$request->checkConfirmEmail();

if (isset ($_POST['name']) && isset ($_POST['email'])) {
	$_POST['name'] = str_replace(" ", "_", $_POST['name']);
	$_POST['name'] = trim(ucfirst($_POST['name']));
	
	global $dontUseWikiDb;
	if( !$dontUseWikiDb ) {
		@ $asSQL->selectDb('enwiki_p');
		$query = "SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED";
		$result = $asSQL->query($query);
		if (!$result) {
			die("ERROR: No result returned.");
		}
	}
	
	// Initialize Variables
	$user = $tsSQL->escape(trim($_POST['name']));
	$email = $tsSQL->escape(trim($_POST['email']));
	
	// Delete old bans
	$tsSQL->query('DELETE FROM `acc_ban` WHERE `ban_duration` < UNIX_TIMESTAMP() AND ban_duration != -1');

	// Check for bans
	$request->isTOR(); // is it a TOR node?
	$request->checkBan('IP',$_SERVER['REMOTE_ADDR']);
	$request->checkBan('Name',$_POST['name']);
	$request->checkBan('EMail',$_POST['email']);
	$request->blockedOnEn();
	
	// Check the blacklists
	$request->checkBlacklist($emailblacklist,$_POST['email'],$_POST['email'],'Email-Bl');
	$request->checkBlacklist($nameblacklist,$_POST['name'],$_POST['email'],'Name-Bl');
	$request->doDnsBlacklistCheck();

	$request->finalChecks($user,$email);

	$request->insertRequest($user,$email);
} else {
	$request->displayform();
	echo $messages->getMessage(22);
	die();
}
?>
