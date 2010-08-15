<?

if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} //Web clients die.

// This task is intended to clone [[User:SQLBot-Hello]],
// and while the code has been completely rewritten, the
// design and functionality of this bot is very similar
// to that of the original bot created by [[User:SQL]].

function WelcomeUser($theUser, $theMessage) {
	global $wiki;
	$talkPage = $wiki->initPage("User talk:$theUser");
	$user = $wiki->initUser($theUser);
	echo "Delivering welcome message to $theUser.\n\n";
	if ($talkPage->exists()) {
		echo "User talk page already exists, stopping message delivery.\n\n";
	} elseif (!$user->exists()) {
		echo "User does not exist, stopping message delivery.\n\n";
	} else {
		$summary = "([[User:MessageDeliveryBot/2|Bot]]) Welcoming user created at [[WP:ACC]].";
		try {
			$talkPage->edit($theMessage, $summary);
		} catch (EditError $e) {
			$errorMessage = $e->getMessage();
			echo "Editing error - $errorMessage\n\n";
		} catch (CURLError $e) {
			echo "Connection error.\n\n";
		}
	}
}

require('config.inc.php');
require('functions.php');
require("$peachyPath/Init.php");

$acc = mysql_connect($toolserver_hostname, $toolserver_username, $toolserver_password) or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_select_db($toolserver_database, $acc);
$result = mysql_query("SELECT welcome_id, welcome_user, welcome_sig, welcome_template, welcome_uid FROM acc_welcome WHERE welcome_status = 'Open';");

if (mysql_num_rows($result) != 0) {
	$wiki = Peachy::newWiki("WelcomerBot");
	$runPage = $wiki->initPage("User:WelcomerBot/Run");
	$templates = templatesarray($acc);
	while($row = mysql_fetch_row($result)) {
		if ($runPage->get_text() == 'go') {
			$theid = $row[0];
			$user = $row[1];
			$signature = html_entity_decode($row[2]);
			$template = $row[3];
			$username = $row[4];
		
			mysql_query("UPDATE acc_welcome SET welcome_status = 'Closed' WHERE welcome_id = '" . $theid . "';");
			
			$templateCode = eval($templates[$template][1]);
			if ($templateCode == NULL) {
				$templateCode = "== Welcome! ==\n\n{{subst:Welcome|$username}}$signature ~~~~~";
			}
			WelcomerUser($user, $templateCode);
			
		} else {
			echo "Run page doesn't say go, stopping bot.\n\n";
			break;
		}
	}
} else {
	echo "No requests need processing.\n\n";
}
echo "Run complete, exiting.\n\n";
?>