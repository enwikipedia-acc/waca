<?php
ini_set('display_errors',1);

require_once '../config.inc.php';
mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
@ mysql_select_db($toolserver_database) or sqlerror(mysql_error(), "Error selecting database.");
mysql_query("ALTER TABLE `acc_user` ALTER COLUMN `user_welcome_templateid` SET DEFAULT '0';");
mysql_query("ALTER TABLE `acc_user` ALTER COLUMN `user_welcome_sig` SET DEFAULT '';");
$result = mysql_query("SELECT `user_id`, `user_welcome` FROM `acc_user`;");
while ($row = mysql_fetch_row($result)) {	
	$userid = $row[0];
	$user_welcome = $row[1];
	if ($user_welcome < 1)
		mysql_query("UPDATE `acc_user` SET `user_welcome_templateid` = '0' WHERE `user_id` = $userid;");
}
mysql_query("ALTER TABLE `acc_user` DROP COLUMN `user_welcome`;");
mysql_close();

echo "done";
?>
