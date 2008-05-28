<?php
include('../../SxWiki.php');
include('sqlbot-hello-enwiki.php');
require_once('../../database.inc');
mysql_connect('sql',$toolserver_username,$toolserver_password);
@mysql_select_db('u_sql') or print mysql_error();
$run = sxGetPage("User:SQLBot-Hello/welcome.run");

function isnewuser($user) {
	$baseurl = 'http://en.wikipedia.org/w/api.php?action=query&prop=revisions&titles=User_talk:' . $user . '&rvprop=timestamp|user|comment|content&format=php' ;
	$revisions = file_get_contents($baseurl);
	$revisions = unserialize($revisions);
	if(isset($revisions['query']['pages']['-1']['missing'])) {
		return(TRUE);
	} else {
		return(FALSE);
	}
}

if ($run != "go") {
	$sqloldtalk = sxGetPage('User talk:SQL');
	$leprop = sxLastEdited('User:SQLBot-Hello/welcome.run');
	$whostoppedme = $leprop[user];
	$whystopme = $leprop[editsum];
	$sqlnewtalk = "\r\n==Help! I've been thwarted!==\r\nHey, as instructed, I need to tell you that [[User:$whostoppedme|$whostoppedme]] has stopped me from running, with an edit summary of $whystopme. ~~~~\r\n";
	$sqlnewtalk = $sqloldtalk . $sqlnewtalk;
	sxPutPage('User talk:SQL', 'Emergency! Bot stopped!', $sqlnewtalk, $null);
	die("\r\nBot stopped by $whostoppedme\r\n"); 
}
function tagpage($user, $template) {
	$basepage = "User_talk:$user";
	$oldpage = sxGetPage($basepage);
	$newbie = isnewuser($user);
	if ($oldpage == "" && $newbie === TRUE) { 
		$newpage = $oldpage . "\n\n$template\n\n";
		echo "Editing page\n";
		sxPutPage($basepage, "BOT: Welcoming user created at [[WP:ACC]].", $newpage);
	} else {
		echo "Skipping $user, not empty.\n";
	}
}

$query = "SELECT * FROM acc_welcome WHERE welcome_status = 'Open';";
$result = mysql_query($query);
if(!$result) Die("ERROR: No result returned.");
while ($row = mysql_fetch_assoc($result)) {
	$user = $row[welcome_user];
	echo "Welcoming $user\n";
	$sig = $row[welcome_sig];
#	$sig = str_replace('|','&#124;',$sig);
	//Addition by Cobi
	$d = 0; 
	$s = ''; 
	for ($i=0;$i<strlen($sig);$i++) { 
		if ($sig{$i} == '[') { $d++; }
		if ($sig{$i} == ']') { $d--; }
		if (($sig{$i} == '|') and ($d == 0)) {
			$s.='<nowiki>|</nowiki>'; 
		} else { 
			$s.=$sig{$i}; 
		} 
	}
	$sig = $s;
	$sid = $row[welcome_uid];
	$template = $row[welcome_template];
	if ($template == "welcomeg") {
		tagpage($user, "{{subst:User:SQLBot-Hello/Welcomeg|sig=$sig ~~~~~}}");
	}
	if ($template == "welcome") {
		tagpage($user, "{{subst:Welcome|$sid}}$sig ~~~~~");
	}
	if ($template == "welcome-personal") {
		tagpage($user, "{{subst:Welcome-personal|$sid}}$sig ~~~~~");
	}
	if ($template == "werdan7") {
		tagpage($user, "{{subst:User:Werdan7/Wel}}$sig ~~~~~");
	}
	if ($template == "welcomemenu") {
		tagpage($user, "{{subst:User:SQL/ACC/WelcomeMenu|sig=$sig ~~~~~}}");
	}
	if ($template == "welcomeicon") {
		tagpage($user, "{{subst:WelcomeIcon}} $sig ~~~~~");
	}
	if ($template == "welcomeshout") {
		tagpage($user, "{{subst:WelcomeShout|$sid}} $sig ~~~~~");
	}
	if ($template == "welcomeshort") {
		tagpage($user, "{{subst:Welcomeshort|$sid}} $sig ~~~~~");
	}
	if ($template == "welcomesmall") {
		tagpage($user, "{{subst:Welcomesmall|$sid}} $sig ~~~~~");
	}
	if ($template == "hopes") {
		tagpage($user, "{{subst:Hopes Welcome}} $sig ~~~~~");
	}
	if ($template == "w-riana") {
		tagpage($user, "{{subst:User:Riana/Welcome|name=$sid|sig=$sig ~~~~~}}");
	}
	if ($template == "wodup") {
		tagpage($user, "{{subst:User:WODUP/Welcome}} $sig ~~~~~");
	}
	if ($template == "w-kk") {
		tagpage($user, "{{subst:User:KrakatoaKatie/Welcome1}} $sig ~~~~~");
	}
	if ($template == "w-screen") {
		tagpage($user, "{{subst:w-screen|sig=$sig ~~~~~}}");
	}
	$query2 = "UPDATE acc_welcome SET welcome_status = 'Closed' WHERE welcome_id = '$row[welcome_id]';";
	$result2 = mysql_query($query2);
	if(!$result) Die("ERROR: No result returned.");
}
?>
