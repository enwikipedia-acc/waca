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

global $session;

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

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
Offline::check(false);

// Initialize the database classes.
$tsSQL = new database("toolserver");
$asSQL = new database("antispoof");

// Creates database links for later use.
$tsSQLlink = $tsSQL->getLink();
$asSQLlink = $asSQL->getLink();

// Initialize the class objects.
$skin     = new skin();
$bskin     = new BootstrapSkin();

if( isset( $_SESSION['user'] ) ) {
	$sessionuser = $_SESSION['user'];
} else {
	$sessionuser = "";
}

BootstrapSkin::displayInternalHeader();

// protect against logged out users
if( !$session->hasright($sessionuser, "Admin") && !$session->hasright($sessionuser, "User")) {
    showlogin();
    BootstrapSkin::displayInternalFooter();
	die();
}

///////////////// Page code

$smarty->display("search/header.tpl");
BootstrapSkin::pushTagStack("</div>"); // span12
BootstrapSkin::pushTagStack("</div>"); // row
    
if( isset($_GET['term']) && isset($_GET['type']) ) 
{
    $term = $_GET['term'];
    
	if($term == "" || $term == "%") 
    {
        BootstrapSkin::displayAlertBox( "No search term entered.","alert-error","",false );
        $smarty->display("search/searchform.tpl");
        BootstrapSkin::displayInternalFooter();
		die();
	}

	if( $_GET['type'] == "email") 
    {
		if($term == "@") 
        {
            BootstrapSkin::displayAlertBox("The search term '@' is not valid for email address searches!");
            $smarty->display("search/searchform.tpl");
            BootstrapSkin::displayInternalFooter();
			die();
		}			

        $qterm = '%' . $term . '%';
        
        $statement = gGetDb()->prepare("SELECT * FROM request WHERE email LIKE :term;");
        $statement->bindParam(":term", $qterm);
        $statement->execute();
        $requests = $statement->fetchAll(PDO::FETCH_CLASS, "Request");
        foreach($requests as $r)
        {
            $r->setDatabase(gGetDb());   
        }
        
        $smarty->assign("term", $term);
        $smarty->assign("requests", $requests);
        $target = "email address";
        $smarty->assign("target", $target);
        
		$smarty->display("search/searchresult.tpl");
	}
	elseif( $_GET['type'] == 'IP') 
    {
		// move this to here, so non-admins can perform searches, but not on IP addresses or emails
		if( ! User::getCurrent()->isAdmin() && ! User::getCurrent()->isCheckuser() ) {
			// Displays both the error message and the footer of the interface.
            BootstrapSkin::displayAlertBox("IP address search is only available to tool admins and checkusers.", "alert-error", "Access Denied");
            $smarty->display("search/searchform.tpl");
            BootstrapSkin::displayInternalFooter();
			die();
		}
        
        $qterm = '%' . $term . '%';
        
        $statement = gGetDb()->prepare("SELECT * FROM request WHERE email <> 'acc@toolserver.org' and ip <> '127.0.0.1' and ip LIKE :term or forwardedip LIKE :term2;");
        $statement->bindParam(":term", $qterm);
        $statement->bindParam(":term2", $qterm);
        $statement->execute();
        $requests = $statement->fetchAll(PDO::FETCH_CLASS, "Request");
        foreach($requests as $r)
        {
            $r->setDatabase(gGetDb());   
        }
        
        $smarty->assign("term", $term);
        $smarty->assign("requests", $requests);
        $target = "IP address";
        $smarty->assign("target", $target);
        
		$smarty->display("search/searchresult.tpl");
	}
	elseif( $_GET['type'] == 'Request') 
    {
        $qterm = '%' . $term . '%';
        
        $statement = gGetDb()->prepare("SELECT * FROM request WHERE name LIKE :term;");
        $statement->bindParam(":term", $qterm);
        $statement->execute();
        $requests = $statement->fetchAll(PDO::FETCH_CLASS, "Request");
        foreach($requests as $r)
        {
            $r->setDatabase(gGetDb());   
        }
        
        $smarty->assign("term", $term);
        $smarty->assign("requests", $requests);
        $target = "requested name";
        $smarty->assign("target", $target);
        
		$smarty->display("search/searchresult.tpl");
	}
	else
	{
        BootstrapSkin::displayAlertBox("Unknown search type", "alert-error", "Error");
		$smarty->display("search/searchform.tpl");
        BootstrapSkin::displayInternalFooter();
		die();
	}
}
else 
{
    $smarty->display("search/searchform.tpl");
}

BootstrapSkin::displayInternalFooter();
?>