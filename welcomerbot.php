<?php

if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} //Web clients die.

ini_set('display_errors', 1);

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

$db = new Database($toolserver_host, $toolserver_username, $toolserver_password, $toolserver_database);
if(!$db) trigger_error($db->lastError(), E_USER_ERROR);

$res = $db->select(
	'acc_welcome',
	'*',
	array('welcome_status' => 'Open')
);

if(count($res)) {
	$wiki = Peachy::newWiki("WelcomerBot");
	$templates = templatesarray($acc);
	foreach( $res as $row ) {
		$theid = $row['welcome_id'];
		$user = $row['welcome_user'];
		$signature = html_entity_decode($row['welcome_sig']);
		$templateID = $row['welcome_template'];
		$username = $row['welcome_uid'];

		$db->update(
			'acc_welcome',
			array('welcome_status' => 'Closed'),
			array('welcome_id' => $theid)
		);

		$templateCode = $templates[$templateID][1];
		eval("\$templateCode = \"$templateCode\";");
		if ($templateCode == NULL) {
			$templateCode = "== Welcome! ==\n\n{{subst:Welcome|$username}}$signature ~~~~~";
		}
		WelcomeUser($user, $templateCode);
	}
} else {
	echo "No requests need processing.\n";
}
echo "Run complete, exiting.\n";

?>