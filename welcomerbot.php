<?

if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} // Web clients die.

ini_set('display_errors', 1);

// This task is intended to clone [[User:SQLBot-Hello]],
// and while the code has been completely rewritten, the
// design and functionality of this bot is very similar
// to that of the original bot created by [[User:SQL]].

function WelcomeUser($theUser, $theCreator, $theMessage) {
	global $wiki;
	$talkPage = $wiki->initPage("User talk:$theUser");
	$user = $wiki->initUser($theUser);
	echo "Delivering welcome message to $theUser.\n";
	if ($talkPage->get_exists()) {
		echo "User talk page already exists, stopping message delivery.\n";
	} elseif (!$user->exists()) {
		echo "User does not exist, stopping message delivery.\n";
	} else {
		$summary = "[[User:WelcomerBot/1|Bot]]: Welcoming user created at [[WP:ACC]] by [[User:$theCreator|$theCreator]].";
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

echo "Connecting to mysql://$toolserver_username@$toolserver_host/$toolserver_database\n"; // :$toolserver_password

$db = new Database($toolserver_host, $toolserver_username, $toolserver_password, $toolserver_database);
if(!$db) trigger_error($db->lastError(), E_USER_ERROR);
$res = $db->select(
	array('acc_welcome', 'acc_user', 'acc_template'),
	array('welcome_id', 'welcome_user', 'user_onwikiname', 'user_welcome_sig', 'template_botcode'),
	array('welcome_status' => 'Open'),
	array(),
	array('welcome_uid' => 'user_name', 'user_welcome_templateid' => 'template_id')
);

if(count($res)) {
	$wiki = Peachy::newWiki("WelcomerBot");
	foreach($res as $row) {
		$theid = $row['welcome_id'];
		$db->update(
			'acc_welcome',
			array('welcome_status' => 'Closed'),
			array('welcome_id' => $theid)
		);
		
		$user = $row['welcome_user'];
		$creator = $row['user_onwikiname'];
		$signature = html_entity_decode($row['user_welcome_sig']) . ' ~~~~~';
		if (!preg_match("/\[\[[ ]*(w:)?[ ]*(en:)?[ ]*User[ ]*:[ ]*".$creator."[ ]*(\||\]\])/i", $signature))
			$signature = " – [[User:$creator|$creator]] ([[User talk:$creator|talk]])";
		$templateID = $row['user_welcome_templateid'];
		
		$templateCode = $row['template_botcode'];
		$templateCode = str_replace('$signature', $signature, $templateCode);
		$templateCode = str_replace('$username', $creator, $templateCode);
		if (!$templateCode)
			$templateCode = "== Welcome! ==\n\n{{subst:Welcome|$creator}}$signature ~~~~~";
		
		WelcomeUser($user, $creator, $templateCode);
	}
} else {
	echo "No requests need processing.\n";
}

$db->close();
echo "Run complete, exiting.\n";

?>