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
require_once 'includes/database.php';
require_once 'includes/messages.php';
require_once 'includes/skin.php';
require_once 'includes/accbotSend.php';
require_once 'includes/session.php';
require_once 'includes/offlineMessage.php';
require_once 'includes/PdoDatabase.php';

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
$offlineMessage = new offlineMessage(false);
$offlineMessage->check();

// Initialize the database classes.
$tsSQL = new database("toolserver");
$tsSQLlink = $tsSQL->getLink();

// Initialize the class objects.
$messages = new messages();
$accbotSend   = new accbotSend();
$session  = new session();

// Initialize the session data.
session_start();

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
	mail($user->getEmail(), "ACC Account Approved", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been approved by " . User::getCurrent()->getUsername() . ". To login please go to $tsurl/acc.php.\n- The English Wikipedia Account Creation Team", $headers);
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
	if (!isset($_POST['demotereason'])) {
		echo "<h2>Demote Reason</h2><strong>The reason you enter here will be shown in the log. Please keep this in mind.</strong><br />\n<form action=\"users.php?demote=$did\" method=\"post\"><br />\n";
		echo "<textarea name=\"demotereason\" rows=\"20\" cols=\"60\">";
		if (isset($_GET['preload'])) {
			echo htmlentities($_GET['preload']);
		}
		echo "</textarea><br />\n";
		echo "<input type=\"submit\"/><input type=\"reset\"/><br />\n";
		echo "</form>";
		BootstrapSkin::displayInternalFooter();
		die();
	} else {
        $user->demote($_POST['demotereason']);

		BootstrapSkin::displayAlertBox( "Changed " . $user->getUsername() . "'s access to 'User'", "alert-info", "", false);
		
        $accbotSend->send($user->getUsername() . " demoted by " . User::getCurrent()->getUsername() . " because: \"" . $_POST['demotereason'] . "\"");
		
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
    
	elseif (!isset($_POST['suspendreason'])) {
		echo "<h2>Suspend Reason</h2><strong>The user will be shown the reason you enter here. Please keep this in mind.</strong><br />\n<form action=\"users.php?suspend=$did\" method=\"post\"><br />\n";
		echo "<textarea name=\"suspendreason\" rows=\"20\" cols=\"60\">";
		if (isset($_GET['preload'])) {
			echo htmlentities($_GET['preload']);
		}
		echo "</textarea><br />\n";
		echo "<input type=\"submit\" /><input type=\"reset\"/><br />\n";
		echo "</form>";
		BootstrapSkin::displayInternalFooter();
		die();
	} else {
		$user->suspend($_POST['suspendreason']);

		BootstrapSkin::displayAlertBox("Suspended user " . $user->getUsername(), "alert-info", "", false);
		$accbotSend->send($user->getUsername() . " had tool access suspended by " . User::getCurrent()->getUsername() . " because: \"" . $_POST['suspendreason'] . "\"");
		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		mail($user->getEmail(), "ACC Account Suspended", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been suspended by " . User::getCurrent()->getUsername() . " because ".$_POST['suspendreason'].". To contest this suspension please email accounts-enwiki-l@lists.wikimedia.org.\n- The English Wikipedia Account Creation Team", $headers);
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
    
	if (!isset($_POST['declinereason'])) {
		echo "<h2>Decline Reason</h2><strong>The user will be shown the reason you enter here. Please keep this in mind.</strong><br />\n<form action=\"users.php?decline=" . $_GET['decline'] . "\" method=\"post\"><br />\n";
		echo "<textarea name=\"declinereason\" rows=\"20\" cols=\"60\">";
		if (isset($_GET['preload'])) {
			echo htmlentities($_GET['preload']);
		}
		echo "</textarea><br />\n";
		echo "<input type=\"submit\"><input type=\"reset\"/><br />\n";
		echo "</form>";
		BootstrapSkin::displayInternalFooter();
		die();
	} else {
		$declinersn = sanitize();

        $user->decline($_POST['declinereason']);
        
        BootstrapSkin::displayAlertBox("Declined user " . $user->getUsername(), "alert-info", "", false);

        $accbotSend->send($user->getUsername() . " was declined access by " . User::getCurrent()->getUsername() . " because: \"" . $_POST['declinereason'] . "\"");
		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		mail($user->getEmail(), "ACC Account Declined", "Dear " . $user->getOnWikiName() . ",\nYour account " . $user->getUsername() . " has been declined access to the account creation tool by " . User::getCurrent()->getUsername() . " because " . $_POST['declinereason'] . ". For more infomation please email accounts-enwiki-l@lists.wikimedia.org.\n- The English Wikipedia Account Creation Team", $headers);
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
		echo "<form action=\"users.php?rename=" . $_GET['rename'] . "\" method=\"post\">";						
		echo "<div class=\"required\">";
		echo "<label for=\"oldname\">Old Username:</label>";
		echo "<input id=\"oldname\" type=\"text\" readonly=\"readonly\" value=\"" . $user->getUsername() . "\"/>";
		echo "</div>";
		echo "<div class=\"required\">";
		echo "<label for=\"newname\">New Username:</label>";
		echo "<input id=\"newname\" type=\"text\" name=\"newname\"/>";
		echo "</div>";
		echo "<div class=\"submit\">";
		echo "<input type=\"submit\"/>";
		echo "</div>";
		echo "</form>";
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
            
            $commentupdate = $database->prepare("UPDATE acc_cmt SET cmt_user = :newname WHERE cmt_user = :oldname;");
            $commentupdate->bindParam(":newname", $_POST['newname']);
            $commentupdate->bindParam(":oldname", $oldname);
            if(!$commentupdate->execute())
            {
                throw new Exception("comment update failed.");   
            }
		    
		    if (User::getCurrent()->getUsername() == $oldname)
		    {
			    $logentry = "themself to " . $_POST['newname'];
		    }
		    else
		    {
			    $logentry = $oldname . " to " . $_POST['newname'];
		    }
            
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
  <h1>User Management<small> Approve, suspend, promote, demote, etc.</small></h1>
</div>
HTML;

BootstrapSkin::displayAlertBox("If it says you can do it, you can do it. Please use this responsibly.", "alert-warning","This interface is NOT a toy.",true,false);

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

echo '<div class="row-fluid"><div class="span12"><div class="accordion" id="accordion2">';
BootstrapSkin::pushTagStack("</div>");
BootstrapSkin::pushTagStack("</div>");
BootstrapSkin::pushTagStack("</div>");



$query = "SELECT * FROM acc_user WHERE user_level = 'New';";
$result = mysql_query($query, $tsSQLlink);
if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
if (mysql_num_rows($result) != 0){
    echo '<div class="accordion-group"><div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">Open requests</a></div><div id="collapseOne" class="accordion-body collapse in"><div class="accordion-inner">';
	echo "<ol>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$uname = $row['user_name'];
		$uoname = $row['user_onwikiname'];
		$userid = $row['user_id'];
		$conf_revid = $row['user_confirmationdiff'];
		$out = "<li><small>[ <span class=\"request-ban\">$uname</span> / <a class=\"request-src\" href=\"http://$wikiurl/wiki/User:$uoname\">$uoname</a> ]";
		$out .= " <a class=\"request-req\" href=\"$tsurl/users.php?approve=$userid\" onclick=\"return confirm('Are you sure you wish to approve $uname?')\">Approve!</a> - <a class=\"request-req\" href=\"$tsurl/users.php?decline=$userid\">Decline</a> - <a class=\"request-req\" href=\"http://toolserver.org/~tparis/pcount/index.php?name=$uoname&amp;lang=en&amp;wiki=wikipedia\">Count!</a>";
		$out .=" - <a class=\"request-req\" href=\"http://$wikiurl/w/index.php?diff=$conf_revid\">Confirmation diff</a>";
		$out .=" - <a class=\"request-req\" href=\"http://meta.wikimedia.org/wiki/Identification_noticeboard\">ID board</a></small></li>";
		echo "$out\n";
	}
	echo "</ol></div></div></div>\n";
}
echo '<div class="accordion-group"><div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">Users</a></div><div id="collapseTwo" class="accordion-body collapse"><div class="accordion-inner">';


$query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id AND log_action = 'Approved') WHERE user_level = 'User' GROUP BY log_pend ORDER BY log_pend DESC;";
$result = mysql_query($query, $tsSQLlink);
if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
echo "<ol>\n";
while ($row = mysql_fetch_assoc($result)) {
	$uname = $row['user_name'];
	$uoname = $row['user_onwikiname'];
	$userid = $row['user_id'];

	$out = "<li><small>[ <a class=\"request-ban\" href=\"$tsurl/statistics.php?page=Users&amp;user=$userid\">$uname</a> / <a class=\"request-src\" href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
	if( $enableRenames == 1 ) {
		$out .= " <a class=\"request-req\" href=\"$tsurl/users.php?rename=$userid\">Rename!</a> -";
		$out .= " <a class=\"request-req\" href=\"$tsurl/users.php?edituser=$userid\">Edit!</a> -";
	}
	$out .= " <a class=\"request-req\" href=\"$tsurl/users.php?suspend=$userid\">Suspend!</a> - <a class=\"request-req\" href=\"$tsurl/users.php?promote=$userid\" onclick=\"return confirm('Are you sure you wish to promote $uname?')\">Promote!</a> (Approved by $row[log_user])</small></li>";
	echo "$out\n";
}
echo <<<HTML
</ol>
</div>
</div></div>

<div class="accordion-group"><div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseThree">Admins</a></div><div id="collapseThree" class="accordion-body collapse"><div class="accordion-inner">
<p class="muted">Please note: Users marked as checkusers automatically get administrative rights, even if they do not appear in the tool administrators section.</p>
HTML;


$query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id AND log_action = 'Promoted') WHERE user_level = 'Admin' GROUP BY log_pend ORDER BY log_time ASC;";
$result = mysql_query($query, $tsSQLlink);
if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
echo "<ol>\n";
while ($row = mysql_fetch_assoc($result)) {
	$uname = sanitize($row['user_name']);
	$uoname = $row['user_onwikiname'];
	$userid = $row['user_id'];
	$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Suspended';";
	$result2 = mysql_query($query, $tsSQLlink);
	if (!$result2)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$suspended = $row2['COUNT(*)'];

	$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Promoted';";
	$result2 = mysql_query($query, $tsSQLlink);
	if (!$result2)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$promoted = $row2['COUNT(*)'];

	$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Approved';";
	$result2 = mysql_query($query, $tsSQLlink);
	if (!$result2)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$approved = $row2['COUNT(*)'];

	$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Demoted';";
	$result2 = mysql_query($query, $tsSQLlink);
	if (!$result2)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$demoted = $row2['COUNT(*)'];

	$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Declined';";
	$result2 = mysql_query($query, $tsSQLlink);
	if (!$result2)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$declined = $row2['COUNT(*)'];

	$out = "<li><small>[ <a class=\"request-ban\" href=\"$tsurl/statistics.php?page=Users&amp;user=$userid\">".htmlentities($row['user_name'])."</a> / <a class=\"request-src\" href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
	if( $enableRenames == 1 ) {
		$out .= " <a class=\"request-req\" href=\"$tsurl/users.php?rename=$userid\">Rename!</a> -";
		$out .= " <a class=\"request-req\" href=\"$tsurl/users.php?edituser=$userid\">Edit!</a> -";
	}
	$out .= " <a class=\"request-req\" href=\"$tsurl/users.php?suspend=$userid\">Suspend!</a> - <a class=\"request-req\" href=\"users.php?demote=$userid\">Demote!</a> (Promoted by $row[log_user] <span style=\"color:purple;\">[P:$promoted|S:$suspended|A:$approved|Dm:$demoted|D:$declined]</span>)</small></li>";
	echo "$out\n";
}
echo <<<HTML
</ol>
</div>
</div></div>

<div class="accordion-group"><div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseFour">Tool Checkuser access</a></div><div id="collapseFour" class="accordion-body collapse"><div class="accordion-inner">
<p class="muted">Please note: Users marked as checkusers automatically get administrative rights, even if they do not appear in the tool administrators section.</p>
HTML;

$query = "SELECT * FROM acc_user WHERE user_checkuser = '1';";
$result = mysql_query($query, $tsSQLlink);
if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
echo "<ol>\n";
while ($row = mysql_fetch_assoc($result)) {
	$uname = $row['user_name'];
	$uoname = $row['user_onwikiname'];
	$userid = $row['user_id'];
	$out = "<li><small>[ <a class=\"request-ban\" href=\"$tsurl/statistics.php?page=Users&amp;user=$userid\">$uname</a> / <a class=\"request-src\" href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]</small></li>";
	echo "$out\n";
}

echo <<<HTML
</ol>
</div>
</div></div>

<div class="accordion-group"><div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseFive">Suspended accounts</a></div><div id="collapseFive" class="accordion-body collapse"><div class="accordion-inner">
HTML;


//$query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id) WHERE user_level = 'Suspended' AND  log_action = 'Suspended' AND log_id = ANY ( SELECT MAX(log_id) FROM acc_log WHERE log_action = 'Suspended' GROUP BY log_pend ) ORDER BY log_id DESC;";
$query = "SELECT * FROM acc_user JOIN (SELECT * FROM (SELECT * FROM acc_log WHERE log_action = 'Suspended' ORDER BY log_id DESC) AS l GROUP BY log_pend) AS log ON acc_user.user_id = log.log_pend WHERE user_level = 'Suspended';";
$result = mysql_query($query, $tsSQLlink);
if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
echo "<ol>\n";
while ($row = mysql_fetch_assoc($result)) {
	$uname = $row['user_name'];
	$uoname = $row['user_onwikiname'];
	$userid = $row['user_id'];
	$out = "<li><small>[ <a class=\"request-ban\" href=\"$tsurl/statistics.php?page=Users&amp;user=$userid\">$uname</a> / <a class=\"request-src\" href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
	if( $enableRenames == 1 ) {
		$out .= " <a class=\"request-req\" href=\"$tsurl/users.php?rename=$userid\">Rename!</a> -";
		$out .= " <a class=\"request-req\" href=\"$tsurl/users.php?edituser=$userid\">Edit!</a> -";
	}
	$out .= " <a class=\"request-req\" href=\"$tsurl/users.php?approve=$userid\" onclick=\"return confirm('Are you sure you wish to unsuspend $uname?')\">Unsuspend!</a> (Suspended by " . $row['log_user'] . " because \"" . $row['log_cmt'] . "\")</small></li>";
	echo "$out\n";
}
echo <<<HTML
</ol>
</div>
</div></div>

<div class="accordion-group"><div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseSix">Declined accounts</a></div><div id="collapseSix" class="accordion-body collapse"><div class="accordion-inner">
HTML;


$query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id AND log_action = 'Declined') WHERE user_level = 'Declined' GROUP BY log_pend ORDER BY log_id DESC;";
$result = mysql_query($query, $tsSQLlink);
if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
echo "<ol>\n";
while ($row = mysql_fetch_assoc($result)) {
	$uname = sanitize($row['user_name']);
	$uoname = $row['user_onwikiname'];
	$userid = $row['user_id'];
	$out = "<li><small>[ <span class=\"request-ban\">".htmlentities($row['user_name'])."</span> / <a class=\"request-src\" href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
	if( $enableRenames == 1 ) {
		$out .= " <a class=\"request-req\" href=\"users.php?rename=$userid\">Rename!</a> -";
		$out .= " <a class=\"request-req\" href=\"users.php?edituser=$userid\">Edit!</a> -";
	}
	$out .= " <a class=\"request-req\" href=\"users.php?approve=$userid\" onclick=\"return confirm('Are you sure you wish to approve $uname?')\">Approve!</a> (Declined by " . $row['log_user'] . " because \"" . $row['log_cmt'] . "\")</small></li>";
	echo "$out\n";
}
echo "</ol></div></div></div>";

BootstrapSkin::displayInternalFooter();
die();
