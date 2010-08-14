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
require("$peachyPath/Init.php");

$acc = mysql_connect($toolserver_hostname, $toolserver_username, $toolserver_password) or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_select_db($toolserver_database, $acc);
$result = mysql_query("SELECT welcome_id, welcome_user, welcome_sig, welcome_template, welcome_uid FROM acc_welcome WHERE welcome_status = 'Open';");

if (mysql_num_rows($result) != 0) {
	$wiki = Peachy::newWiki("WelcomerBot");
	$runPage = $wiki->initPage("User:WelcomerBot/Run");
	while($row = mysql_fetch_row($result)) {
		if ($runPage->get_text() == 'go') {
			$theid = $row[0];
			$user = $row[1];
			$signature = html_entity_decode($row[2]);
			$template = $row[3];
			$username = $row[4];
		
			mysql_query("UPDATE acc_welcome SET welcome_status = 'Closed' WHERE welcome_id = '" . $theid . "';");
		
			if ($template == "welcomeg") {
			        WelcomeUser($user, "== Welcome! ==\n\n{{subst:User:SQLBot-Hello/Welcomeg|$username|sig=$signature ~~~~~}}");
			} else if ($template == "welcome") {
			        WelcomeUser($user, "{{subst:Welcome|$username}}$signature ~~~~~");
			} else if ($template == "welcome-personal") {
			        WelcomeUser($user, "{{subst:Welcome-personal|$username}}$signature ~~~~~");
			} else if ($template == "werdan7") {
			        WelcomeUser($user, "{{subst:User:Werdan7/Wel}}$signature ~~~~~");
			} else if ($template == "welcomemenu") {
			        WelcomeUser($user, "== Welcome! ==\n\n{{subst:WelcomeMenu|sig=$signature ~~~~~}}");
			} else if ($template == "welcomeicon") {
			        WelcomeUser($user, "== Welcome! ==\n\n{{subst:WelcomeIcon}} $signature ~~~~~");
			} else if ($template == "welcomeshout") {
			        WelcomeUser($user, "{{subst:WelcomeShout|$username}} $signature ~~~~~");
			} else if ($template == "welcomeshort") {
			        WelcomeUser($user, "{{subst:Welcomeshort|$username}} $signature ~~~~~");
			} else if ($template == "welcomesmall") {
			        WelcomeUser($user, "{{subst:Welcomesmall|$username}} $signature ~~~~~");
			} else if ($template == "hopes") {
			        WelcomeUser($user, "{{subst:Hopes Welcome}} $signature ~~~~~");
			} else if ($template == "w-riana") {
			        WelcomeUser($user, "== Welcome! ==\n\n{{subst:User:Riana/Welcome|name=$username|sig=$signature ~~~~~}}");
			} else if ($template == "wodup") {
			        WelcomeUser($user, "{{subst:User:WODUP/Welcome}} $signature ~~~~~");
			} else if ($template == "w-screen") {
			        WelcomeUser($user, "== Welcome! ==\n\n{{subst:w-screen|sig=$signature ~~~~~}}");
			} else if ($template == "williamh") {
			        WelcomeUser($user, "{{subst:User:WilliamH/Welcome|$username}} $signature ~~~~~");
			} else if ($template == "malinaccier") {
			        WelcomeUser($user, "{{subst:User:Malinaccier/Welcome|$signature ~~~~~}}");
			} else if ($template == "welcome!") {
			        WelcomeUser($user, "== Welcome! ==\n\n{{subst:Welcome!|from=$username|ps=$signature ~~~~~}}");
			} else if ($template == "laquatique") {
			        WelcomeUser($user, "{{subst:User:L'Aquatique/welcome}} $signature ~~~~~");
			} else if ($template == "coffee") {
			        WelcomeUser($user, "{{subst:User:Coffee/welcome|$username|||$signature ~~~~~}}");
			} else if ($template == "matt-t") {
			        WelcomeUser($user, "{{subst:User:Matt.T/C}} $signature ~~~~~");
			} else if ($template == "staffwaterboy") {
			        WelcomeUser($user, "{{subst:User:Staffwaterboy/Welcome}} $signature ~~~~~");
			} else if ($template == "maedin") {
			        WelcomeUser($user, "{{subst:User:Maedin/Welcome}} $signature ~~~~~");
			} else if ($template == "chzz") {
			        WelcomeUser($user, "{{subst:User:Chzz/botwelcome|name=$username|sig=$signature ~~~~~}}");
			} else if ($template == 'phantomsteve') {
			        WelcomeUser($user, "{{subst:User:Phantomsteve/bot welcome}} $signature ~~~~~");
			} else if ($template == "hi878") {
			        WelcomeUser($user, "{{subst:User:Hi878/Welcome|$username|$signature ~~~~~}}");
			} else {
			        WelcomeUser($user, "== Welcome! ==\n\n{{subst:Welcome|$username}}$signature ~~~~~");
			}
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