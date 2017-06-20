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

// load the configuration
require_once 'config.inc.php';

// Get all the classes.
require_once 'functions.php';
initialiseSession();
require_once 'includes/PdoDatabase.php';
require_once 'includes/SmartyInit.php';
require_once 'includes/StatisticsPage.php';

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
if (Offline::isOffline()) {
	echo Offline::getOfflineMessage(false);
	die();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'Main';

if (isset($_SERVER['PATH_INFO'])) {
	$page = substr($_SERVER['PATH_INFO'], 1);
}

$sp = StatisticsPage::Create($page);

$sp->Show();
