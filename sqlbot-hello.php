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

#if ($ACC != "1") {
#    header("Location: $tsurl/");
#    die();
#} //Re-route, if you're a web client.

include ('../SxWiki.php');
include ('../sqlbot-hello-enwiki.php');
require_once ('config.inc.php');
mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
@ mysql_select_db($toolserver_database) or print mysql_error();
$run = sxGetPage("User:SQLBot-Hello/welcome.run");

function isnewuser($user) {
	$baseurl = 'http://en.wikipedia.org/w/api.php?action=query&prop=revisions&titles=User_talk:' . $user . '&rvprop=timestamp|user|comment|content&format=php';
	$revisions = file_get_contents($baseurl);
	$revisions = unserialize($revisions);
	if (isset ($revisions['query']['pages']['-1']['missing'])) {
		return (TRUE);
	} else {
		return (FALSE);
	}
}

if ($run != "go") {
	$sqloldtalk = sxGetPage('User talk:SQL');
	$leprop = sxLastEdited('User:SQLBot-Hello/welcome.run');
	$whostoppedme = $leprop['user'];
	$whystopme = $leprop['editsum'];
	$sqlnewtalk = "\r\n==Help! I've been thwarted!==\r\nHey, as instructed, I need to tell you that [[User:$whostoppedme|$whostoppedme]] has stopped me from running, with an edit summary of $whystopme. ~~~~\r\n";
	$sqlnewtalk = $sqloldtalk . $sqlnewtalk;
#	sxPutPage('User talk:SQL', 'Emergency! Bot stopped!', $sqlnewtalk, $null);
	die("\r\nBot stopped by $whostoppedme\r\n");
}
function tagpage($user, $template) {
	$basepage = "User_talk:$user";
	$oldpage = sxGetPage($basepage);
	$user2 = urlencode($user);
	$newbie = isnewuser($user2);
	/*
	$userexist = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=$user2&format=php");
	$ue = unserialize($userexist);
	foreach ($ue[query]['users'] as $oneue) {
	    	if(!isset($oneue[missing])) {
		echo "$user does not exist, skipping!\n"; 
		$exist = FALSE; 
	} else {
	}
	}
	*/
	$exist = TRUE;
	if ($oldpage == "" && $newbie === TRUE && $exist == TRUE) {
		$newpage = $oldpage . "\n\n$template\n\n";
		echo "Editing page\n";
		sxPutPage($basepage, "BOT: Welcoming user created at [[WP:ACC]].", $newpage);
	} else {
		echo "Skipping $user, not empty.\n";
	}
}

$query = "SELECT * FROM acc_welcome WHERE welcome_status = 'Open';";
$result = mysql_query($query);
if (!$result)
	Die("ERROR: No result returned.");
while ($row = mysql_fetch_assoc($result)) {
	$user = $row['welcome_user'];
	echo "Welcoming $user\n";
	$sig = $row['welcome_sig'];
	//Addition by Cobi
	$d = 0;
	$s = '';
	for ($i = 0; $i < strlen($sig); $i++) {
		if ($sig {
			$i }
		== '[') {
			$d++;
		}
		if ($sig {
			$i }
		== ']') {
			$d--;
		}
		if (($sig {
			$i }
		== '|') and ($d == 0)) {
			$s .= '<nowiki>|</nowiki>';
		} else {
			$s .= $sig {
				$i };
		}
	}
	$sig = html_entity_decode($s);
	$sid = $row['welcome_uid'];
	$template = $row['welcome_template'];
	if ($template == "welcomeg") {
		tagpage($user, "{{subst:User:SQLBot-Hello/Welcomeg|sig=$sig ~~~~~}}");
	} else if ($template == "welcome") {
		tagpage($user, "{{subst:Welcome|$sid}}$sig ~~~~~");
	} else if ($template == "welcome-personal") {
		tagpage($user, "{{subst:Welcome-personal|$sid}}$sig ~~~~~");
	} else if ($template == "werdan7") {
		tagpage($user, "{{subst:User:Werdan7/Wel}}$sig ~~~~~");
	} else if ($template == "welcomemenu") {
		tagpage($user, "{{subst:User:SQL/ACC/WelcomeMenu|sig=$sig ~~~~~}}");
	} else if ($template == "welcomeicon") {
		tagpage($user, "{{subst:WelcomeIcon}} $sig ~~~~~");
	} else if ($template == "welcomeshout") {
		tagpage($user, "{{subst:WelcomeShout|$sid}} $sig ~~~~~");
	} else if ($template == "welcomeshort") {
		tagpage($user, "{{subst:Welcomeshort|$sid}} $sig ~~~~~");
	} else if ($template == "welcomesmall") {
		tagpage($user, "{{subst:Welcomesmall|$sid}} $sig ~~~~~");
	} else if ($template == "hopes") {
		tagpage($user, "{{subst:Hopes Welcome}} $sig ~~~~~");
	} else if ($template == "w-riana") {
		tagpage($user, "{{subst:User:Riana/Welcome|name=$sid|sig=$sig ~~~~~}}");
	} else if ($template == "wodup") {
		tagpage($user, "{{subst:User:WODUP/Welcome}} $sig ~~~~~");
	} else if ($template == "w-screen") {
		tagpage($user, "{{subst:w-screen|sig=$sig ~~~~~}}");
	} else if ($template == "williamh") {
		tagpage($user, "{{subst:User:WilliamH/Welcome|$sid}} $sig ~~~~~");
	} else if ($template == "malinaccier") {
		tagpage($user, "{{subst:User:Malinaccier/Welcome|$sig ~~~~~}}");
	} else if ($template == "welcome!") {
		tagpage($user, "{{subst:Welcome!|from=$sid|ps=$sig ~~~~~}}");
	} else if ($template == "laquatique") {
		tagpage($user, "{{subst:User:L'Aquatique/welcome}} $sig ~~~~~");
	} else if ($template == "chetblong") {
		tagpage($user, "{{subst:User:Chet B Long/welcome|$sid|||$sig ~~~~~}}");
	} else if ($template == "matt-t") {
		tagpage($user, "{{subst:User:Matt.T/C}} $sig ~~~~~");
	} else if ($template == "roux") {
		tagpage($user, "{{subst:User:Roux/W}} $sig ~~~~~");
	} else if ($template == "staffwaterboy") {
		tagpage($user, "{{subst:User:Staffwaterboy/Welcome}} $sig ~~~~~");
	} else if ($template == "maedin") {
		tagpage($user, "{{subst:User:Maedin/Welcome}} $sig ~~~~~");
	} else if ($template == "chzz") {
		tagpage($user, "{{subst:user:Chzz/botwelcome|name=$sid|sig=$sig ~~~~~}}");
	} else {
		tagpage($user, "{{subst:Welcome|$sid}}$sig ~~~~~");
	}
	$query2 = "UPDATE acc_welcome SET welcome_status = 'Closed' WHERE welcome_id = '" . $row['welcome_id'] . "';";
	$result2 = mysql_query($query2);
	if (!$result)
		Die("ERROR: No result returned.");
}
?>
