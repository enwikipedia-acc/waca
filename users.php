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
require_once 'includes/database.php';
require_once 'includes/skin.php';
require_once 'includes/accbotSend.php';
require_once 'includes/session.php';
require_once 'lib/mediawiki-extensions-OAuth/lib/OAuth.php';
require_once 'lib/mediawiki-extensions-OAuth/lib/JWT.php';
require_once 'oauth/OAuthUtility.php';

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
Offline::check(false);

// Initialize the database classes.
$tsSQL = new database("toolserver");
$tsSQLlink = $tsSQL->getLink();

// Initialize the class objects.
$accbotSend = new accbotSend();
$session = new session();

#region User search

if(isset($_GET['usersearch']))
{
    $user = User::getByUsername($_GET['usersearch'], gGetDb());
    
    if($user != false)
    {
        header("Location: $baseurl/statistics.php?page=Users&user={$user->getId()}");
        die();
    }
}

#endregion

// Display the header of the interface.
BootstrapSkin::displayInternalHeader();

// A content block is created if the action is none of the above.
// This block would later be used to keep all the HTML except the header and footer.
$out = "<div class=\"row-fluid\"><div id=\"span12\">";
BootstrapSkin::pushTagStack("</div>");
BootstrapSkin::pushTagStack("</div>");
echo $out;

#region Checks if the current user has admin rights.

if( User::getCurrent() == false ) 
{
    showlogin();
    BootstrapSkin::displayInternalFooter();
	die();
}

if( ! User::getCurrent()->isAdmin() )
{
	// Displays both the error message and the footer of the interface.
    BootstrapSkin::displayAlertBox("I'm sorry, but, this page is restricted to administrators only.", "alert-error", "Access Denied",true,false);
    BootstrapSkin::displayInternalFooter();
	die();
}
#endregion

#region user access actions

if (isset ($_GET['approve'])) 
{
    $user = User::getById($_GET['approve'], gGetDb());
    
    if($user == false)
    {
        BootstrapSkin::displayAlertBox("Sorry, the user you are trying to approve could not be found.", "alert-error", "Error",true,false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    if($user->isUser() || $user->isAdmin())
    {
        BootstrapSkin::displayAlertBox("Sorry, the user you are trying to approve has already been approved.", "alert-error", "Error",true,false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    $user->approve();
    
    BootstrapSkin::displayAlertBox("Approved user " . $user->getUsername(), "alert-info", "", false);

    $accbotSend->send($user->getUsername() . " approved by " . User::getCurrent()->getUsername());
	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	mail($user->getEmail(), "ACC Account Approved", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been approved by " . User::getCurrent()->getUsername() . ". To login please go to $baseurl/acc.php.\n- The English Wikipedia Account Creation Team", $headers);
    BootstrapSkin::displayInternalFooter();
    die();
}

if (isset ($_GET['demote'])) 
{
    $user = User::getById($_GET['demote'], gGetDb());
    
    if( $user == false)
    {
        BootstrapSkin::displayAlertBox("Sorry, the user you are trying to demote could not be found.", "alert-error", "Error",true,false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    if(!$user->isAdmin())
    {
        BootstrapSkin::displayAlertBox("Sorry, the user you are trying to demote is not an admin.", "alert-error", "Error",true,false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
	$did = $_GET['demote']; // clean because we've already pulled back a user from the database.
	if (!isset($_POST['reason'])) {

        global $smarty;
        $smarty->assign("user", $user);
        $smarty->assign("status", "User");
        $smarty->assign("action", "demote");
        $smarty->display("usermanagement/changelevel-reason.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	} else {
        $user->demote($_POST['reason']);

		BootstrapSkin::displayAlertBox( "Changed " . $user->getUsername() . "'s access to 'User'", "alert-info", "", false);
		
        $accbotSend->send($user->getUsername() . " demoted by " . User::getCurrent()->getUsername() . " because: \"" . $_POST['reason'] . "\"");
		
        $headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		mail($user->getEmail(), "ACC Account Demoted", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been demoted by " . User::getCurrent()->getUsername() . " because " . User::getCurrent()->getUsername() . ". To contest this demotion please email accounts-enwiki-l@lists.wikimedia.org.\n- The English Wikipedia Account Creation Team", $headers);
		BootstrapSkin::displayInternalFooter();
		die();
	}
}

if (isset ($_GET['suspend'])) {
	$did = sanitize($_GET['suspend']);
    $user = User::getById($_GET['suspend'], gGetDb());
    
    if($user == false)
    {
        BootstrapSkin::displayAlertBox("Sorry, the user you are trying to suspend could not be found.", "alert-error", "Error",true,false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    if($user->isSuspended())
    {
        BootstrapSkin::displayAlertBox("Sorry, the user you are trying to suspend is already suspended.", "alert-error", "Error",true,false);
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
	} else {
		$user->suspend($_POST['reason']);

		BootstrapSkin::displayAlertBox("Suspended user " . $user->getUsername(), "alert-info", "", false);
		$accbotSend->send($user->getUsername() . " had tool access suspended by " . User::getCurrent()->getUsername() . " because: \"" . $_POST['reason'] . "\"");
		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		mail($user->getEmail(), "ACC Account Suspended", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been suspended by " . User::getCurrent()->getUsername() . " because ".$_POST['reason'].". To contest this suspension please email accounts-enwiki-l@lists.wikimedia.org.\n- The English Wikipedia Account Creation Team", $headers);
		BootstrapSkin::displayInternalFooter();
		die();
	}
}

if (isset ($_GET['promote'])) {
    $user = User::getById($_GET['promote'], gGetDb());
    
    if($user == false)
    {
        BootstrapSkin::displayAlertBox("Sorry, the user you are trying to promote could not be found.", "alert-error", "Error",true,false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
	if ($user->isAdmin()) {
        BootstrapSkin::displayAlertBox("Sorry, the user you are trying to promote has Administrator access.", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}		
    
    $user->promote();
    
	BootstrapSkin::displayAlertBox($user->getUsername() . " promoted to 'Admin'", "alert-info", "", false);
	$accbotSend->send($user->getUsername() . " promoted to admin by " . User::getCurrent()->getUsername());
	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	mail($user->getEmail(), "ACC Account Promoted", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been promted to admin status by " . User::getCurrent()->getUsername() . ".\n- The English Wikipedia Account Creation Team", $headers);
    die();
}

if (isset ($_GET['decline'])) {
    $user = User::getById($_GET['decline'], gGetDb());
    
    if($user == false)
    {
        BootstrapSkin::displayAlertBox("Sorry, the user you are trying to decline could not be found.", "alert-error", "Error",true,false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
	if ($user->isAdmin()) {
        BootstrapSkin::displayAlertBox("Sorry, the user you are trying to decline is not new.", "alert-error", "Error", true, false);
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
	} else {
        $user->decline($_POST['reason']);
        
        BootstrapSkin::displayAlertBox("Declined user " . $user->getUsername(), "alert-info", "", false);

        $accbotSend->send($user->getUsername() . " was declined access by " . User::getCurrent()->getUsername() . " because: \"" . $_POST['reason'] . "\"");
		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		mail($user->getEmail(), "ACC Account Declined", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been declined access to the account creation tool by " . User::getCurrent()->getUsername() . " because " . $_POST['reason'] . ". For more infomation please email accounts-enwiki-l@lists.wikimedia.org.\n- The English Wikipedia Account Creation Team", $headers);
		BootstrapSkin::displayInternalFooter();
		die();
	}
}

#endregion

#region renaming

if ( isset ($_GET['rename']) && $enableRenames == 1 ) 
{
    $user = User::getById($_GET['rename'], gGetDb());
    
    if($user == false)
    {
        BootstrapSkin::displayAlertBox("Sorry, the user you are trying to rename could not be found.", "alert-error", "Error", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
	if (!isset($_POST['newname'])) 
    {
		global $smarty;
        $smarty->assign("user", $user);
        $smarty->display("usermanagement/renameuser.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
    else 
    {
        if(!isset($_POST['newname']) || trim($_POST['newname']) == "")
        {
            BootstrapSkin::displayAlertBox("The new username cannot be empty.", "alert-error", "Error", true, false);
            BootstrapSkin::displayInternalFooter();
            die();
        }
        
        if(User::getByUsername($_POST['newname'], gGetDb()) != false)
        {
            BootstrapSkin::displayAlertBox("Username already exists.", "alert-error", "Error", true, false);
            BootstrapSkin::displayInternalFooter();
            die();
        }
        
        $database = gGetDb();
        
        if(!$database->beginTransaction())
        {
            BootstrapSkin::displayAlertBox("Database transaction could not be started.", "alert-error", "Error", true, false);
            BootstrapSkin::displayInternalFooter();
            die();
        }
        
        try
        {
            $oldname = $user->getUsername();
            
            $user->setUsername($_POST['newname']);
            $user->save();
            
            $tgtmessage = "User " . $_GET['rename'] . " (" . $oldname . ")";
            $logpendupdate = $database->prepare("UPDATE acc_log SET log_pend = :newname WHERE log_pend = :tgtmessage AND log_action != 'Renamed'");
            $logpendupdate->bindParam(":newname", $_POST['newname']);
            $logpendupdate->bindParam(":tgtmessage", $tgtmessage);
            if(!$logpendupdate->execute())
            {
                throw new Exception("log_pend update failed.");   
            }
        
            $logupdate = $database->prepare("UPDATE acc_log SET log_user = :newname WHERE log_user = :oldname;");
            $logupdate->bindParam(":newname", $_POST['newname']);
            $logupdate->bindParam(":oldname", $oldname);
            if(!$logupdate->execute())
            {
                throw new Exception("log_user update failed.");   
            }
            
            $logentry = serialize(array('old' => $oldname, 'new' => $_POST['newname']));
            
            $userid = $user->getId();
            $currentUser = User::getCurrent()->getUsername();
            $logentryquery = $database->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES (:userid, :siuser, 'Renamed', CURRENT_TIMESTAMP(), :logentry);");
            $logentryquery->bindParam(":logentry", $logentry);
            $logentryquery->bindParam(":userid", $userid);
            $logentryquery->bindParam(":siuser", $currentUser);
            if(!$logentryquery->execute())
            {
                throw new Exception("logging failed.");   
            }
            
		    BootstrapSkin::displayAlertBox("Changed User " . htmlentities($oldname,ENT_COMPAT,'UTF-8') . " name to ". htmlentities($_POST['newname'],ENT_COMPAT,'UTF-8') , "alert-info","",false);        
        }
        catch (Exception $ex)
        {
            $database->rollBack();
            BootstrapSkin::displayAlertBox($ex->getMessage(), "alert-error", "Error", true, false);
            BootstrapSkin::displayInternalFooter();
            die();
        }
        
        $database->commit();
        
		if (User::getCurrent()->getId() == $user->getId())
		{
			$accbotSend->send(User::getCurrent()->getUsername() . " changed their username to " . $_POST['newname']);
		}
		else
		{
			$accbotSend->send(User::getCurrent()->getUsername() . " changed " . $oldname . "'s username to " . $_POST['newname']);
		}
        
		BootstrapSkin::displayInternalFooter();
		die();
	}
}

#endregion

#region edit user

if (isset ($_GET['edituser']) && $enableRenames == 1) {
    $user = User::getById($_GET['edituser'], gGetDb());
    
    if($user == false)
    {
        BootstrapSkin::displayAlertBox("Sorry, the user you are trying to rename could not be found.", "alert-error", "Error", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
	if ($_SERVER['REQUEST_METHOD'] != "POST") {
        global $smarty;
        $smarty->assign("user", $user);
        $smarty->display("usermanagement/edituser.tpl");
	} else {
        $database = gGetDb();
        if(!$database->beginTransaction())
        {
            BootstrapSkin::displayAlertBox("Database transaction could not be started.", "alert-error", "Error", true, false);
            BootstrapSkin::displayInternalFooter();
            die();
        }
        
        try
        {
            $user->setEmail($_POST['user_email']);
            $user->setOnWikiName($_POST['user_onwikiname']);
            $user->save();
       
            $siuser = User::getCurrent()->getUsername();
            $logquery = $database->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES (:gid, :sid, 'Prefchange', CURRENT_TIMESTAMP(), '');");
            $logquery->bindParam(":gid", $_GET['edituser']);
            $logquery->bindParam(":sid", $siuser);
            if(!$logquery->execute()) throw new Exception("Logging failed.");
        
		    $accbotSend->send($siuser . " changed preferences for " . $user->getUsername());
		    BootstrapSkin::displayAlertBox("Changes saved.", "alert-info");
        }
        catch (Exception $ex)
        {
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

// ---------------------   USER MANAGEMENT MAIN PAGE -----------------------------------------

echo <<<HTML
<div class="page-header">
  <h1>User Management<small> Approve, suspend, promote, demote, etc.&nbsp;<a class="btn btn-primary" href="?showall"><i class="icon-white icon-eye-open"></i>&nbsp;Show all</a></small></h1>
</div>
HTML;

BootstrapSkin::displayAlertBox("If it says you can do it, you can do it. Please use this responsibly.", "alert-warning","This interface is NOT a toy.",true,false);

// assign to user
$userListQuery = "SELECT username FROM user;";
$userListResult = gGetDb()->query($userListQuery);
$userListData = $userListResult->fetchAll(PDO::FETCH_COLUMN);
$userListProcessedData = array();
foreach ($userListData as $userListItem)
{
    $userListProcessedData[] = "\"" . htmlentities($userListItem, ENT_QUOTES) . "\"";
}

$jsuserlist = '[' . implode(",", $userListProcessedData) . ']';

echo <<<HTML
<div class="row-fluid">
    <form class="form-search">
        <input type="text" class="input-large" placeholder="Jump to user" data-provide="typeahead" data-items="10" data-source='{$jsuserlist}' name="usersearch">
        <button type="submit" class="btn">Search</button>
    </form>
</div>
HTML;

/**
 * CURRENTLY UNUSED!!
 * 
 * Shows A list of users in a table with the relevant buttons for that access level.
 * 
 * Uses smarty
 * 
 * Different levels may require the use of different data attributes.
 * 
 * @param $data An array of arrays (see example)
 * @param $level The user access level
 * @example showUserList( array(
 *          1 => array(
 *              "username" => "foo",
 *              "onwikiname" => "foo",
 *              ),
 *          )
 *          
 */
function showUserList($data, $level) {
       global $smarty;
       $smarty->assign("listuserlevel", $level);
       $smarty->assign("listuserdata", $data);
       $smarty->display("usermanagement-userlist.tpl");
}

global $smarty;
echo '<div class="row-fluid"><div class="span12"><div class="accordion" id="accordion2">';
BootstrapSkin::pushTagStack("</div>");
BootstrapSkin::pushTagStack("</div>");
BootstrapSkin::pushTagStack("</div>");

$database = gGetDb();

$result = User::getAllWithStatus("New", $database);

if($result != false && count($result) != 0)
{
    echo '<div class="accordion-group"><div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">Open requests</a></div><div id="collapseOne" class="accordion-body collapse in"><div class="accordion-inner">';
    
    $smarty->assign("userlist", $result);
    $smarty->display("usermanagement/userlist.tpl");
	echo "</div></div></div>\n";
}
echo '<div class="accordion-group"><div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">Users</a></div><div id="collapseTwo" class="accordion-body collapse"><div class="accordion-inner">';

$result = User::getAllWithStatus("User", $database);
$smarty->assign("userlist", $result);
$smarty->display("usermanagement/userlist.tpl");
echo <<<HTML
</div>
</div></div>

<div class="accordion-group"><div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseThree">Admins</a></div><div id="collapseThree" class="accordion-body collapse"><div class="accordion-inner">
<p class="muted">Please note: Users marked as checkusers automatically get administrative rights, even if they do not appear in the tool administrators section.</p>
HTML;

$result = User::getAllWithStatus("Admin", $database);
$smarty->assign("userlist", $result);
$smarty->display("usermanagement/userlist.tpl");
echo <<<HTML
</div>
</div></div>

<div class="accordion-group"><div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseFour">Tool Checkuser access</a></div><div id="collapseFour" class="accordion-body collapse"><div class="accordion-inner">
<p class="muted">Please note: Users marked as checkusers automatically get administrative rights, even if they do not appear in the tool administrators section.</p>
HTML;

$result = User::getAllCheckusers( $database );
$smarty->assign("userlist", $result);
$smarty->display("usermanagement/userlist.tpl");
echo '</div></div></div>';

if(isset($_GET['showall']))
{
    echo <<<HTML
<div class="accordion-group"><div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseFive">Suspended accounts</a></div><div id="collapseFive" class="accordion-body collapse"><div class="accordion-inner">
HTML;

    $result = User::getAllWithStatus("Suspended", $database);
    $smarty->assign("userlist", $result);
    $smarty->display("usermanagement/userlist.tpl");
    echo <<<HTML
</div>
</div></div>

<div class="accordion-group"><div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseSix">Declined accounts</a></div><div id="collapseSix" class="accordion-body collapse"><div class="accordion-inner">
HTML;

    $result = User::getAllWithStatus("Declined", $database);
    $smarty->assign("userlist", $result);
    $smarty->display("usermanagement/userlist.tpl");
    echo "</div></div></div>";
}

BootstrapSkin::displayInternalFooter();
die();
