<?php
require_once('config.inc.php');
require_once('includes/StatisticsPage.php');
require_once('includes/messages.php');
require_once('includes/database.php');
require_once('functions.php');
require_once('devlist.php');

readOnlyMessage();

$messages = new messages();
global $toolserver_host, $toolserver_username, $toolserver_password,$toolserver_database;
$tsSQL = new database( $toolserver_host, $toolserver_username, $toolserver_password);
$tsSQL->selectDb($toolserver_database);

global $dontUseWikiDb;
if($dontUseWikiDb == 0)
{
	global $antispoof_host, $antispoof_db;
	$asSQL = new database($antispoof_host, $toolserver_username, $toolserver_password);
	$asSQL->selectDb($antispoof_db);
}

$sp = StatisticsPage::Create(isset($_GET['page']) ? $_GET['page'] : 'Main');

$sp->Show();