<?php
// API for helpmebot/other bots/etc
// This is a public-data-only read-only API, much in the same vein of ACCBot was.
//
// count - Displays statistics for the targeted user.
// status - Displays interface statistics, such as the number of open requests.
// stats - Gives a readout similar to the user list user information page.

require_once("config.inc.php");
require_once("includes/PdoDatabase.php");

// setup

$database = new PdoDatabase("mysql:host=".$toolserver_host.";dbname=".$toolserver_database,$toolserver_username, $toolserver_password);

// use exceptions on failed database queries
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header("Content-Type: text/xml");

$document = new DomDocument('1.0');
$doc_api = $document->createElement("api");
$document->appendChild($doc_api);


switch($_GET['action'])
{
	case "count":
		actionCount();
		break;
	case "status":
		actionStatus();
		break;
	case "stats":
		actionStats();
		break;
	default:
		$doc_api->setAttribute("actions", "count, status, stats");
		break;
}

echo $document->saveXml();


function actionStatus()
{
	global $database, $document, $doc_api;
	
	$docStatus = $document->createElement("status");
	$doc_api->appendChild($docStatus);
	
	$status = "Open";			
	$mailconfirm = "Confirmed";			
	$query = $database->prepare("SELECT COUNT(*) AS count FROM acc_pend WHERE pend_status = :pstatus AND pend_mailconfirm = :pmailconfirm;");
	$query->bindParam(":pstatus", $status);
	$query->bindParam(":pmailconfirm", $mailconfirm);
	$query->execute();
	$sus = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
	$docStatus->setAttribute("open", $sus['count']);

	$status = "Admin";			
	$query->execute();
	$sus = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
	$docStatus->setAttribute("admin", $sus['count']);

	$status = "Checkuser";			
	$query->execute();
	$sus = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
	$docStatus->setAttribute("checkuser", $sus['count']);

	$query = $database->prepare("SELECT COUNT(*) AS count FROM acc_ban");
	$query->execute();
	$sus = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
	$docStatus->setAttribute("bans", $sus['count']);

	$level = "Admin";
	$query = $database->prepare("SELECT COUNT(*) AS count FROM acc_user WHERE user_level = :ulevel;");
	$query->bindParam(":ulevel",$level);
	$query->execute();
	$sus = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
	$docStatus->setAttribute("useradmin", $sus['count']);
	
	$level = "User";
	$query->execute();
	$sus = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
	$docStatus->setAttribute("user", $sus['count']);
	
	$level = "New";
	$query->execute();
	$sus = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
	$docStatus->setAttribute("usernew", $sus['count']);

}

function actionStats()
{
	global $document, $doc_api, $database;

	$username = isset( $_GET['user'] ) ? $_GET['user'] : '';
	if( $username == '' ) {
		$err = $document->createElement("error");
		$doc_api->appendChild($err);
		$err->setAttribute("error", "Please specify a username");
		return;
	}

	$username = trim($username); //Strip any whitespace from the username.  

	$docUser = $document->createElement("user");
	$doc_api->appendChild($docUser);
	// verify is a user
	
	$isUserQuery = $database->prepare("SELECT COUNT(*) AS count FROM acc_user WHERE user_name = :username;");
	$isUserQuery->bindParam(":username", $username);
	$isUserQuery->execute();
	
	$isUser = $isUserQuery->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );

	$isUser = ( ( $isUser['count'] == 0 ) ? false : true );

	if( $isUser ) {
		$userQuery = $database->prepare("SELECT user_name, user_level, user_lastactive, user_welcome_templateid, user_onwikiname FROM acc_user WHERE user_name = :username;");
		$userQuery->bindParam(":username", $username);
		$userQuery->execute();
		
		$user = $userQuery->fetch(PDO::FETCH_ASSOC) or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
		
		foreach($user as $key => $value)
			$docUser->setAttribute($key, $value);
			
	} else {
		$docUser->setAttribute("missing","true");
	}
}

function actionCount( ) {
	global $document, $doc_api, $database;

	$username = isset( $_GET['user'] ) ? $_GET['user'] : '';
	if( $username == '' ) {
		$err = $document->createElement("error");
		$doc_api->appendChild($err);
		$err->setAttribute("error", "Please specify a username");
		return;
	}

	$username = trim($username); //Strip any whitespace from the username.  

	$docUser = $document->createElement("user");
	$doc_api->appendChild($docUser);
	$docUser->setAttribute("name", $username);
	// verify is a user
	
	$isUserQuery = $database->prepare("SELECT COUNT(*) AS count FROM acc_user WHERE user_name = :username;");
	$isUserQuery->bindParam(":username", $username);
	$isUserQuery->execute();
	
	$isUser = $isUserQuery->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );

	$isUser = ( ( $isUser['count'] == 0 ) ? false : true );

	if( $isUser ) {
		// accounts created
		$query = $database->prepare("SELECT COUNT(*) AS count FROM acc_log WHERE (log_action = 'Closed 1' OR log_action = 'Closed custom-y') AND log_user = :username");
		$query->bindParam(":username", $username);
		$query->execute();

		$count = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
		$docUser->setAttribute("count",$count['count']);

		$query = $database->prepare("SELECT * FROM acc_user WHERE user_name = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		
		$user = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );

			
		$adminInfo = '';
		$docUser->setAttribute("level",$user['user_level']);
		if( $user['user_level'] == 'Admin' ) {
			$action = "Suspended";			
			$query = $database->prepare("SELECT COUNT(*) AS count FROM acc_log WHERE log_user = :username AND log_action = :action");
			$query->bindParam(":username", $username);
			$query->bindParam(":action", $action);
			$query->execute();
			$sus = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("suspended", $sus['count']);

			$action = "Promoted";	
			$query->execute();
			$pro = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("promoted",$pro['count']);

			$action = "Approved";	
			$query->execute(); 
			$app = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("approved",$app['count']);

			$action = "Demoted";	
			$query->execute();
			$dem = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("demoted",$dem['count']);

			$action = "Declined";	
			$query->execute();
			$dec = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("declined",$dec['count']);

			$action = "Renamed";	
			$query->execute();
			$rnc = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("renamed",$rnc['count']);
			
			$action = "Edited";	
			$query->execute();
			$mec = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("edited",$mec['count']);
			
			$action = "Prefchange";
			$query->execute();
			$pcc = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("prefchange",$pcc['count']);
		}

		$query = $database->prepare("SELECT COUNT(*) AS count FROM acc_log WHERE log_time LIKE :date  AND (log_action = 'Closed 1' OR log_action = 'Closed custom-y') AND log_user = :username");
		$query->bindParam(":username", $username);
		$date = date( 'Y-m-d' ) . "%";
		$query->bindParam(":date", $date );
		$query->execute();
		$today = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
		$docUser->setAttribute("today",$today['count']);
	} else {
		$docUser->setAttribute("missing","true");
	}
}
