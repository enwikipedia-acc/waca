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
	default:
		$doc_api->setAttribute("actions", "");
		break;
}

echo $document->saveXml();


function commandCount( ) {
	global $document, $doc_api;;

	$username = isset( $_GET['user'] ) ? $_GET['user'] : '';
	if( $username == '' ) {
		die("please specify a username");
	}

	$username = trim($username); //Strip any whitespace from the username.  

	// verify is a user
	
	$isUserQuery = $database->prepare("SELECT COUNT(*) AS count FROM acc_user WHERE user_name = :username;");
	$isUserQuery->bindParam(":username", $username);
	$isUserQuery->execute();
	
	$isUser = $isUserQuery->fetch() or die( 'MySQL Error: ' . PDO::errorInfo()[2] . "\n" );

	$isUser = ( ( $isUser['count'] == 0 ) ? false : true );

	if( $isUser ) {
		// accounts created
		$query = $database->prepare("SELECT COUNT(*) AS count FROM acc_log WHERE (log_action = 'Closed 1' OR log_action = 'Closed custom-y') AND log_user = :username");
		$query->bindParam(":username", $username);
		$query->execute();

		$count = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo()[2] . "\n" );
	
		$count = $count['count'];

		$query = $database->prepare("SELECT * FROM acc_user WHERE user_name = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		
		$user = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo()[2] . "\n" );

			
		$adminInfo = '';
		if( $user['user_level'] == 'Admin' ) {
			$query = $database->prepare("SELECT COUNT(*) AS count FROM acc_log WHERE log_user = :username AND log_action = :action");
			$query->bindParam(":username", $username);
			
			$query->bindParam(":action", "Suspended");
			$query->execute();
			$sus = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo()[2] . "\n" );
			$sus = $sus['count'];

			$query->bindParam(":action", "Promoted");
			$query->execute();
			$pro = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo()[2] . "\n" );
			$pro = $pro['count'];

			$query->bindParam(":action", "Approved");
			$query->execute();
			$app = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo()[2] . "\n" );
			$app = $app['count'];

			$query->bindParam(":action", "Demoted");
			$query->execute();
			$dem = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo()[2] . "\n" );
			$dem = $dem['count'];

			$query->bindParam(":action", "Declined");
			$query->execute();
			$dec = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo()[2] . "\n" );
			$dec = $dec['count'];

			$query->bindParam(":action", "Renamed");
			$query->execute();
			$rnc = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo()[2] . "\n" );
			$rnc = $rnc['count'];
			
			$query->bindParam(":action", "Edited");
			$query->execute();
			$mec = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo()[2] . "\n" );
			$mec = $mec['count'];
			
			$query->bindParam(":action", "Prefchange");
			$query->execute();
			$pcc = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo()[2] . "\n" );
			$pcc = $pcc['count'];

			$adminInfo = 'Suspended: ' . $sus . ', Promoted: ' . $pro . ', Approved: ' . $app . ', Demoted: ' . $dem . ', Declined: ' . $dec . ', Renamed: ' . $rnc . ', Messages Edited: ' . $mec . ', Preferences Edited: ' . $pcc;
		}

		$query = $database->prepare("SELECT COUNT(*) AS count FROM acc_log WHERE log_time LIKE :date  AND (log_action = 'Closed 1' OR log_action = 'Closed custom-y') AND log_user = :username");
		$query->bindParam(":username", $username);
		$query->bindParam(":date", date( 'Y-m-d' ) . "%" );
		$query->execute();
		$today = $query->fetch() or die( 'MySQL Error: ' . PDO::errorInfo()[2] . "\n" );
		$today = $today['count'];

	echo( 'PRIVMSG ' . $parsed['to'] . ' :' . html_entity_decode($username) . ' (' . $user['user_level'] . ') has closed ' . $count
			. ' requests as \'Created\', ' . ( ( $today == 0 ) ? 'none' : $today ) . ' of them today. ' . $adminInfo );
	} else {
		echo( 'PRIVMSG ' . $parsed['to'] . ' :' . html_entity_decode($username) . ' is not a valid username.' );
	}
}
