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

$document = new DomDocument('1.0');
$doc_api = $document->createElement("api");
$document->appendChild($doc_api);


switch($_GET['action'])
{
	case "count":
		actionCount();
		break;
	default:
		$doc_api->setAttribute("actions", "count");
		break;
}

echo $document->saveXml();


function actionCount( ) {
	global $document, $doc_api;;

	$username = isset( $_GET['user'] ) ? $_GET['user'] : '';
	if( $username == '' ) {
		die("please specify a username");
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
	
		$count = $count['count'];

		$query = $database->prepare("SELECT * FROM acc_user WHERE user_name = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		
		$user = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );

			
		$adminInfo = '';
		$docUser->setAttribute("level",$user['user_level']);
		if( $user['user_level'] == 'Admin' ) {
			$query = $database->prepare("SELECT COUNT(*) AS count FROM acc_log WHERE log_user = :username AND log_action = :action");
			$query->bindParam(":username", $username);
			
			$query->bindParam(":action", "Suspended");
			$query->execute();
			$sus = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("suspended", $sus['count']);

			$query->bindParam(":action", "Promoted");
			$query->execute();
			$pro = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("promoted",$pro['count']);

			$query->bindParam(":action", "Approved");
			$query->execute(); 
			$app = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("approved",$app['count']);

			$query->bindParam(":action", "Demoted");
			$query->execute();
			$dem = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("demoted",$dem['count']);

			$query->bindParam(":action", "Declined");
			$query->execute();
			$dec = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("declined",$dec['count']);

			$query->bindParam(":action", "Renamed");
			$query->execute();
			$rnc = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("renamed",$rnc['count']);
			
			$query->bindParam(":action", "Edited");
			$query->execute();
			$mec = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("edited",$mec['count']);
			
			$query->bindParam(":action", "Prefchange");
			$query->execute();
			$pcc = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
			$docUser->setAttribute("prefchange",$pcc['count']);
		}

		$query = $database->prepare("SELECT COUNT(*) AS count FROM acc_log WHERE log_time LIKE :date  AND (log_action = 'Closed 1' OR log_action = 'Closed custom-y') AND log_user = :username");
		$query->bindParam(":username", $username);
		$query->bindParam(":date", date( 'Y-m-d' ) . "%" );
		$query->execute();
		$today = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo() . "\n" );
		$docUser->setAttribute("today",$today['count']);
	} else {
		$docUser->setAttribute("missing","true");
	}
}
