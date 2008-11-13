<?php

/*
* Stats implementation by Simon Walker
* Released into public domain as part of the ACC package
*/

require_once ('../config.inc.php');

mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
@ mysql_select_db($toolserver_database) or print mysql_error();


$date = new DateTime();
$date->modify("-45 days");


$query = "SELECT `user_id` as 'tooluserid', `user_name` as 'tooluser', `user_level` AS 'toolaccesslevel', CONCAT('User:', `user_onwikiname`) AS 'enwikiuser', `user_lastactive` as 'lasttoollogon'
FROM `acc_user` 
WHERE 
     user_lastactive < '".$date->format("Y-m-d H:i:s")."' 
 and user_level != 'Suspended'
 and user_level != 'Declined'
 and user_level != 'New'
ORDER BY user_lastactive ASC
;
";

$result = mysql_query($query);
if (!$result)
	Die("ERROR: No result returned.");
echo "<table><tr><th>User ID</th><th>Tool Username</th><th>User access level</th><th>enwiki username</th><th>Last activity</th></tr>";
while ($r = mysql_fetch_assoc($result)) {
	echo "<tr><th>".$r['tooluserid']."</th><td>".$r[tooluser]."</td><td>".$r['toolaccesslevel']."</td><td>".$r['enwikiuser']."</td><td>".$r['lasttoollogon']."</td></tr>";
}
echo "</table>";