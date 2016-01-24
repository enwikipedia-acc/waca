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

// Initialize the session data.
session_start();

// Get all the classes.
require_once 'functions.php';
require_once 'includes/PdoDatabase.php';
require_once 'includes/SmartyInit.php';
require_once 'includes/session.php';

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
if (Offline::isOffline()) {
	echo Offline::getOfflineMessage(false);
	die();
}

// Initialize the class objects.
$session = new session();

#region User search

if (isset($_GET['usersearch'])) {
	$user = User::getByUsername($_GET['usersearch'], gGetDb());

	if ($user != false) {
		header("Location: $baseurl/statistics.php?page=Users&user={$user->getId()}");
		die();
	}
}

#endregion

if (User::getCurrent()->isCommunityUser()) {
	ob_end_clean();
	global $baseurl;
	header("Location: $baseurl/internal.php/login");
	die();
}

// Display the header of the interface.
BootstrapSkin::displayInternalHeader();

// A content block is created if the action is none of the above.
// This block would later be used to keep all the HTML except the header and footer.
$out = "<div class=\"row-fluid\"><div id=\"span12\">";
BootstrapSkin::pushTagStack("</div>");
BootstrapSkin::pushTagStack("</div>");
echo $out;

#region Checks if the current user has admin rights.


if (!User::getCurrent()->isAdmin()) {
	// Displays both the error message and the footer of the interface.
	BootstrapSkin::displayAlertBox(
			"I'm sorry, but, this page is restricted to administrators only.", 
			"alert-error", 
			"Access Denied",
			true,
			false);
	BootstrapSkin::displayInternalFooter();
	die();
}
#endregion


#region edit user

if (isset ($_GET['edituser']) && $enableRenames == 1) {
	$user = User::getById($_GET['edituser'], gGetDb());

	if ($user == false) {
		BootstrapSkin::displayAlertBox(
			"Sorry, the user you are trying to rename could not be found.", 
			"alert-error", 
			"Error", 
			true, 
			false);
		BootstrapSkin::displayInternalFooter();
		die();
	}

	if ($_SERVER['REQUEST_METHOD'] != "POST") {
		global $smarty;
		$smarty->assign("user", $user);
		$smarty->display("usermanagement/edituser.tpl");
	}
	else {
		$database = gGetDb();
		if (!$database->beginTransaction()) {
			BootstrapSkin::displayAlertBox(
				"Database transaction could not be started.", 
				"alert-error", 
				"Error", 
				true, 
				false);
			BootstrapSkin::displayInternalFooter();
			die();
		}

		try {
			$user->setEmail($_POST['user_email']);

			if (!$user->isOAuthLinked()) {
				$user->setOnWikiName($_POST['user_onwikiname']);
			}

			$user->save();

			Logger::userPreferencesChange($database, $user);
            
			Notification::userPrefChange($user);
			BootstrapSkin::displayAlertBox("Changes saved.", "alert-info");
		}
		catch (Exception $ex) {
			$database->rollBack();
			BootstrapSkin::displayAlertBox($ex->getMessage(), "alert-error", "Error", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}

		$database->commit();
	}
	BootstrapSkin::displayInternalFooter();
	die();
}

#endregion
