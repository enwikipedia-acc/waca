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
require_once ( 'devlist.php' );

// check to see if the database is unavailable
readOnlyMessage();

// retrieve database connections
global $tsSQLlink, $asSQLlink, $toolserver_database;
list($tsSQLlink, $asSQLlink) = getDBconnections();
@ mysql_select_db($toolserver_database) or sqlerror(mysql_error(),"Error selecting database.");

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

$result = mysql_query($query, $tsSQLlink);
if (!$result)
	Die("ERROR: No result returned.");
displayheader();
echo '<h2>Old user accounts</h2>This list contains the usernames of all accounts that have not logged in in the past 45 days.';

echo "<table><tr><th>User ID</th><th>Tool Username</th><th>User access level</th><th>enwiki username</th><th>Last activity</th><th>Approval</th></tr>";
$currentrow = 0;
while ($r = mysql_fetch_assoc($result)) {

	$tooluser = $r['tooluser'];
	global $regdevlist;
	if(! array_search_recursive( $tooluser, $regdevlist) )
	{
		$userid = $r['tooluserid'];
		$q2 = 'select log_time from acc_log where log_pend = '.$userid.' and log_action = "Approved" order by log_id desc limit 1;';
		$res2 = mysql_query($q2, $tsSQLlink);
		if (!$res2)
			die("ERROR: No result returned.");
		$row2 = mysql_fetch_assoc($res2);
		$approved = $row2['log_time'];
		
		$appr_array = date_parse($approved);
		$appr_ts = mktime($appr_array['hour'], $appr_array['minute'], $appr_array['second'], $appr_array['month'], $appr_array['day'], $appr_array['year'] );
		
		if( $appr_ts < mktime($date->format("H"), $date->format("i"), $date->format("s"), $date->format("m"), $date->format("d"), $date->format("Y") )) {
			$currentrow +=1;
			echo "<tr";		
			if ($currentrow % 2 == 0) {
				echo ' class="even">';
			} else {
				echo ' class="odd">';
			}	
			echo "<th>$userid</th><td>$tooluser</td><td>".$r['toolaccesslevel']."</td><td>".$r['enwikiuser']."</td><td>".$r['lasttoollogon']."</td><td>".$approved."</td></tr>";
		}
	}
}
echo "</table>";
displayfooter();
?>
