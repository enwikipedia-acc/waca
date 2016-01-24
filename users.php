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

#region user access actions

if (isset ($_GET['approve'])) {
	$user = User::getById($_GET['approve'], gGetDb());

	if ($user == false) {
		BootstrapSkin::displayAlertBox(
			"Sorry, the user you are trying to approve could not be found.", 
			"alert-error", 
			"Error",
			true,
			false);
		BootstrapSkin::displayInternalFooter();
		die();
	}

	if ($user->isUser() || $user->isAdmin()) {
		BootstrapSkin::displayAlertBox(
			"Sorry, the user you are trying to approve has already been approved.", 
			"alert-error", 
			"Error",
			true,
			false);
		BootstrapSkin::displayInternalFooter();
		die();
	}

	$user->approve();

	BootstrapSkin::displayAlertBox(
		"Approved user " . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'), 
		"alert-info", 
		"", 
		false);

	Notification::userApproved($user);

	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	// TODO: move to template?
	mail($user->getEmail(), "ACC Account Approved", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been approved by " . User::getCurrent()->getUsername() . ". To login please go to $baseurl/acc.php.\n- The English Wikipedia Account Creation Team", $headers);
	BootstrapSkin::displayInternalFooter();
	die();
}

if (isset ($_GET['demote'])) {
	$user = User::getById($_GET['demote'], gGetDb());

	if ($user == false) {
		BootstrapSkin::displayAlertBox(
			"Sorry, the user you are trying to demote could not be found.", 
			"alert-error", 
			"Error",
			true,
			false);
		BootstrapSkin::displayInternalFooter();
		die();
	}

	if (!$user->isAdmin()) {
		BootstrapSkin::displayAlertBox(
			"Sorry, the user you are trying to demote is not an admin.", 
			"alert-error", 
			"Error",
			true,
			false);
		BootstrapSkin::displayInternalFooter();
		die();
	}

	if (!isset($_POST['reason'])) {

		global $smarty;
		$smarty->assign("user", $user);
		$smarty->assign("status", "User");
		$smarty->assign("action", "demote");
		$smarty->display("usermanagement/changelevel-reason.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
	else {
		$user->demote($_POST['reason']);

		BootstrapSkin::displayAlertBox( 
			"Changed " . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8') . "'s access to 'User'", 
			"alert-info", 
			"", 
			false);

		Notification::userDemoted($user, $_POST['reason']);

		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
        
		// TODO: move to template?
		mail($user->getEmail(), "ACC Account Demoted", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been demoted by " . User::getCurrent()->getUsername() . " because " . User::getCurrent()->getUsername() . ". To contest this demotion please email accounts-enwiki-l@lists.wikimedia.org.\n- The English Wikipedia Account Creation Team", $headers);
		BootstrapSkin::displayInternalFooter();
		die();
	}
}

if (isset ($_GET['suspend'])) {
	$user = User::getById($_GET['suspend'], gGetDb());

	if ($user == false) {
		BootstrapSkin::displayAlertBox(
			"Sorry, the user you are trying to suspend could not be found.", 
			"alert-error", 
			"Error",
			true,
			false);
		BootstrapSkin::displayInternalFooter();
		die();
	}

	if ($user->isSuspended()) {
		BootstrapSkin::displayAlertBox(
			"Sorry, the user you are trying to suspend is already suspended.", 
			"alert-error", 
			"Error",
			true,
			false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	elseif (!isset($_POST['reason'])) {
		global $smarty;
		$smarty->assign("user", $user);
		$smarty->assign("status", "Suspended");
		$smarty->assign("action", "suspend");
		$smarty->display("usermanagement/changelevel-reason.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
	else {
		$user->suspend($_POST['reason']);

		Notification::userSuspended($user, $_POST['reason']);
		BootstrapSkin::displayAlertBox(
			"Suspended user " . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'), 
			"alert-info", 
			"", 
			false);

		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
        
		// TODO: move to template?
		mail($user->getEmail(), "ACC Account Suspended", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been suspended by " . User::getCurrent()->getUsername() . " because " . $_POST['reason'] . ". To contest this suspension please email accounts-enwiki-l@lists.wikimedia.org.\n- The English Wikipedia Account Creation Team", $headers);
		BootstrapSkin::displayInternalFooter();
		die();
	}
}

if (isset ($_GET['promote'])) {
	$user = User::getById($_GET['promote'], gGetDb());

	if ($user == false) {
		BootstrapSkin::displayAlertBox(
			"Sorry, the user you are trying to promote could not be found.", 
			"alert-error", 
			"Error",
			true,
			false);
		BootstrapSkin::displayInternalFooter();
		die();
	}

	if ($user->isAdmin()) {
		BootstrapSkin::displayAlertBox(
			"Sorry, the user you are trying to promote has Administrator access.",
			"alert-error", 
			"Error", 
			true, 
			false);
		BootstrapSkin::displayInternalFooter();
		die();
	}

	$user->promote();

	Notification::userPromoted($user);

	BootstrapSkin::displayAlertBox(
		htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8') . " promoted to 'Admin'", 
		"alert-info", 
		"", 
		false);

	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
    
	// TODO: move to template?
	mail($user->getEmail(), "ACC Account Promoted", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been promted to admin status by " . User::getCurrent()->getUsername() . ".\n- The English Wikipedia Account Creation Team", $headers);
	die();
}

if (isset ($_GET['decline'])) {
	$user = User::getById($_GET['decline'], gGetDb());

	if ($user == false) {
		BootstrapSkin::displayAlertBox(
			"Sorry, the user you are trying to decline could not be found.", 
			"alert-error", 
			"Error",
			true,
			false);
		BootstrapSkin::displayInternalFooter();
		die();
	}

	if ($user->isAdmin()) {
		BootstrapSkin::displayAlertBox("Sorry, the user you are trying to decline is not new.", 
			"alert-error", 
			"Error", 
			true, 
			false);
		BootstrapSkin::displayInternalFooter();
		die();
	}

	if (!isset($_POST['reason'])) {
		global $smarty;
		$smarty->assign("user", $user);
		$smarty->assign("status", "Declined");
		$smarty->assign("action", "decline");
		$smarty->display("usermanagement/changelevel-reason.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
	else {
		$user->decline($_POST['reason']);

		Notification::userDeclined($user, $_POST['reason']);

		BootstrapSkin::displayAlertBox(
			"Declined user " . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'), 
			"alert-info", 
			"", 
			false);

		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
        
		// TODO: move to template?
		mail($user->getEmail(), "ACC Account Declined", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been declined access to the account creation tool by " . User::getCurrent()->getUsername() . " because " . $_POST['reason'] . ". For more infomation please email accounts-enwiki-l@lists.wikimedia.org.\n- The English Wikipedia Account Creation Team", $headers);
		BootstrapSkin::displayInternalFooter();
		die();
	}
}

#endregion

#region renaming

if (isset ($_GET['rename'])) {
	$user = User::getById($_GET['rename'], gGetDb());

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

	if (!isset($_POST['newname'])) {
		global $smarty;
		$smarty->assign("user", $user);
		$smarty->display("usermanagement/renameuser.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
	else {
		if (!isset($_POST['newname']) || trim($_POST['newname']) == "") {
			BootstrapSkin::displayAlertBox("The new username cannot be empty.", "alert-error", "Error", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}

		if (User::getByUsername($_POST['newname'], gGetDb()) != false) {
			BootstrapSkin::displayAlertBox("Username already exists.", "alert-error", "Error", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}

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
			$oldname = $user->getUsername();

			$user->setUsername($_POST['newname']);
			$user->save();

			$logentry = serialize(array('old' => $oldname, 'new' => $_POST['newname']));
			Logger::renamedUser($database, $user, $logentry);
           
			BootstrapSkin::displayAlertBox(
				"Changed User " 
					. htmlentities($oldname, ENT_COMPAT, 'UTF-8') 
					. " name to "
					. htmlentities($_POST['newname'], ENT_COMPAT, 'UTF-8'), 
				"alert-info",
				"",
				false);
		}
		catch (Exception $ex) {
			$database->rollBack();
			BootstrapSkin::displayAlertBox($ex->getMessage(), "alert-error", "Error", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}

		$database->commit();

		Notification::userRenamed($user, $oldname);

		BootstrapSkin::displayInternalFooter();
		die();
	}
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
