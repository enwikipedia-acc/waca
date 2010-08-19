<?php

if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} //Web clients die.

ini_set('display_errors',1);

// This task is intended to clone [[User:SQLBot-Hello]],
// and while the code has been completely rewritten, the
// design and functionality of this bot is very similar
// to that of the original bot created by [[User:SQL]].

function WelcomeUser($theUser, $theMessage) {
	global $wiki;
	$talkPage = $wiki->initPage("User talk:$theUser");
	$user = $wiki->initUser($theUser);
	echo "Delivering welcome message to $theUser.\n";
	if ($talkPage->exists()) {
		echo "User talk page already exists, stopping message delivery.\n";
	} elseif (!$user->exists()) {
		echo "User does not exist, stopping message delivery.\n";
	} else {
		$summary = "[[User:WelcomerBot/1|Bot]]: Welcoming user created at [[WP:ACC]].";
		try {
			$talkPage->edit($theMessage, $summary);
		} catch (EditError $e) {
			$errorMessage = $e->getMessage();
			echo "Editing error - $errorMessage\n";
		} catch (CURLError $e) {
			echo "Connection error.\n";
		}
	}
}

require_once('config.inc.php');
require_once('functions.php');
require_once("$peachyPath/Init.php");

echo "Connecting to mysql://$toolserver_username:$toolserver_password@$toolserver_host/$toolserver_database\n";

$acc = mysql_connect($toolserver_host, $toolserver_username, $toolserver_password) or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_select_db($toolserver_database, $acc);
$result = mysql_query("SELECT * FROM acc_welcome WHERE welcome_status = 'Open';");

if (mysql_num_rows($result) != 0) {
	$wiki = Peachy::newWiki("WelcomerBot");
	$runPage = $wiki->initPage("User:WelcomerBot/Run");
	$templates = templatesarray($acc);
	while($row = mysql_fetch_assoc($result)) {
		if ($runPage->get_text() == 'go') {
			$theid = $row['welcome_id'];
			$user = $row['welcome_user'];
			$signature = html_entity_decode($row['welcome_sig']);
			$templateID = $row['welcome_template'];
			$username = $row['welcome_uid'];
		
			mysql_query("UPDATE acc_welcome SET welcome_status = 'Closed' WHERE welcome_id = '" . $theid . "';");
			
			$templateCode = $templates[$templateID][1];
			eval("\$templateCode = \"$templateCode\";");
			if ($templateCode == NULL) {
				$templateCode = "== Welcome! ==\n\n{{subst:Welcome|$username}}$signature ~~~~~";
			}
			WelcomeUser($user, $templateCode);
			
		} else {
			echo "Run page doesn't say go, stopping bot.\n";
			break;
		}
	}
} else {
	echo "No requests need processing.\n";
}
echo "Run complete, exiting.\n";
?>
