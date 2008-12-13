<?php

    /**************************************************************
    ** English Wikipedia Account Request Interface               **
    ** Wikipedia Account Request Graphic Design by               **
    ** Charles Melbye is licensed under a Creative               **
    ** Commons Attribution-Noncommercial-Share Alike             **
    ** 3.0 United States License. All other code                 **
    ** released under Public Domain by the ACC                   **
    ** Development Team.                                         **
    **             Developers:                                   **
    **  SQL ( http://en.wikipedia.org/User:SQL )                 **
    **  Cobi ( http://en.wikipedia.org/User:Cobi )               **
    ** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
    **FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
    **Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
    **Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
    **Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
    **OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
    **Prodego ( http://en.wikipedia.org/wiki/User:Prodego )         **
        **FunPika ( http://en.wikipedia.org/wiki/User:FunPika )      **
    **                                                           **
    **************************************************************/

require_once ( 'config.inc.php' );
require_once ( 'functions.php' );

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
displayheader();
echo '<h2>Old user accounts</h2>This list contains the usernames of all accounts that have not logged in in the past 45 days.';

echo "<table><tr><th>User ID</th><th>Tool Username</th><th>User access level</th><th>enwiki username</th><th>Last activity</th></tr>";
while ($r = mysql_fetch_assoc($result)) {
	echo "<tr><th>".$r['tooluserid']."</th><td>".$r['tooluser']."</td><td>".$r['toolaccesslevel']."</td><td>".$r['enwikiuser']."</td><td>".$r['lasttoollogon']."</td></tr>";
}
echo "</table>";
displayfooter();
?>