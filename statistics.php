<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/

// Get all the classes.
require_once 'config.inc.php';
require_once 'functions.php';
require_once 'includes/SmartyInit.php';
require_once 'includes/StatisticsPage.php';
require_once 'includes/messages.php';
require_once 'includes/database.php';
require_once 'devlist.php';
require_once 'includes/offlineMessage.php';

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
$offlineMessage = new offlineMessage(false);
$offlineMessage->check();

// Initialize the class objects.
$messages = new messages();

// Initialize the database classes.
$tsSQL = new database("toolserver");
$asSQL = new database("antispoof");

// Creates database links for use by functions.php. 
$tsSQLlink = $tsSQL->getLink();
$asSQLlink = $asSQL->getLink();

$page = isset($_GET['page']) ? $_GET['page'] : 'Main';

if(isset($_SERVER['PATH_INFO']))
{
	$page = substr($_SERVER['PATH_INFO'],1);
}

$sp = StatisticsPage::Create($page);

$sp->Show();
?>