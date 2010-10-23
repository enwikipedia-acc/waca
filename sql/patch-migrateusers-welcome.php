<?php
if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} // Web clients die.
ini_set('display_errors',1);

require_once '../config.inc.php';
mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
@ mysql_select_db($toolserver_database) or sqlerror(mysql_error(),"Error selecting database.");
mysql_query("ALTER TABLE `acc_user` ADD COLUMN `user_welcome_templateid` int(11) NOT NULL DEFAULT '1';");
$result = mysql_query("SELECT `user_id`, `user_welcome_template` FROM `acc_user`;");
while ($row = mysql_fetch_row($result)) {	
	$userid = $row[0];
	$oldfield = $row[1];

	// initialise this to 1, so one user's change doesn't go to the next user who doesn't have it set
	$newfield='1';

	if($oldfield == 'welcome') {
		$newfield = '1';
	} elseif($oldfield == 'welcomeg') {
		$newfield = '2';
	} elseif($oldfield == 'welcome-personal') {
		$newfield = '3';
	} elseif($oldfield == 'werdan7') {
		$newfield = '4';
	} elseif($oldfield == 'welcomemenu') {
		$newfield = '5';
	} elseif($oldfield == 'welcomeicon') {
		$newfield = '6';
	} elseif($oldfield == 'welcomeshout') {
		$newfield = '7';
	} elseif($oldfield == 'welcomeshort') {
		$newfield = '8';
	} elseif($oldfield == 'welcomesmall') {
		$newfield = '9';
	} elseif($oldfield == 'hopes') {
		$newfield = '10';
	} elseif($oldfield == 'w-riana') {
		$newfield = '11';
	} elseif($oldfield == 'wodup') {
		$newfield = '12';
	} elseif($oldfield == 'w-screen') {
		$newfield = '13';
	} elseif($oldfield == 'williamh') {
		$newfield = '14';
	} elseif($oldfield == 'malinaccier') {
		$newfield = '15';
	} elseif($oldfield == 'welcome!') {
		$newfield = '16';
	} elseif($oldfield == 'laquatique') {
		$newfield = '17';
	} elseif($oldfield == 'coffee') {
		$newfield = '18';
	} elseif($oldfield == 'matt-t') {
		$newfield = '19';
	} elseif($oldfield == 'staffwaterboy') {
		$newfield = '20';
	} elseif($oldfield == 'maedin') {
		$newfield = '21';
	} elseif($oldfield == 'chzz') {
		$newfield = '22';
	} elseif($oldfield == 'phantomsteve') {
		$newfield = '23';
	} elseif($oldfield == 'hi878') {
		$newfield = '24';
	} elseif($oldfield == 'fridaesdoom') {
		$newfield = '25';
	} elseif($oldfield == 'rockdrum') {
		$newfield = '26';
	}
	mysql_query("UPDATE `acc_user` SET `user_welcome_templateid` = $newfield WHERE `user_id` = $userid;");
}
mysql_close();

echo "done";
?>
