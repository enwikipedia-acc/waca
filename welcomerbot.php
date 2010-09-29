<?

if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} //Web clients die.

ini_set('display_errors', 1);

// This task is intended to clone [[User:SQLBot-Hello]],
// and while the code has been completely rewritten, the
// design and functionality of this bot is very similar
// to that of the original bot created by [[User:SQL]].

function WelcomeUser($theUser, $theMessage) {
	global $wiki, $username;
	$talkPage = $wiki->initPage("User talk:$theUser");
	$user = $wiki->initUser($theUser);
	echo "Delivering welcome message to $theUser.\n";
	if ($talkPage->exists()) {
		echo "User talk page already exists, stopping message delivery.\n";
	} elseif (!$user->exists()) {
		echo "User does not exist, stopping message delivery.\n";
	} else {
		$summary = "[[User:WelcomerBot/1|Bot]]: Welcoming user created at [[WP:ACC]] by [[User:$username|$username]].";
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
	array('acc_welcome', 'acc_user'),
	array('welcome_id', 'welcome_uid', 'welcome_user', 'user_welcome_sig', 'user_welcome_template'),
	array('welcome_status' => 'Open'),
	array(),
	array('welcome_uid' => 'user_name')
);

if(count($res)) {
	$wiki = Peachy::newWiki("WelcomerBot");
	$templates = templatesarray();
	foreach($res as $row) {
		$theid = $row['welcome_id'];
		$db->update(
			'acc_welcome',
			array('welcome_status' => 'Closed'),
			array('welcome_id' => $theid)
		);
		
		$user = $row['welcome_user'];
		$username = $row['welcome_uid'];
		$signature = html_entity_decode($row['user_welcome_sig']);
		if (!preg_match("/\[\[[ ]*(w:)?[ ]*(en:)?[ ]*User[ ]*:[ ]*".$username."[ ]*(\||\]\])/i", $signature)) {
			$signature = " – [[User:$username|$username]] ([[User talk:$username|talk]])";
		}
		$templateID = $row['user_welcome_template'];
		
		$templateCode = $templates[$templateID][1];
		eval("\$templateCode = \"$templateCode\";");
		if (!$templateCode) {
			$templateCode = "== Welcome! ==\n\n{{subst:Welcome|$username}}$signature ~~~~~";
		}
		
		WelcomeUser($user, $templateCode);
	}
} else {
	echo "No requests need processing.\n";
}
echo "Run complete, exiting.\n";

?>