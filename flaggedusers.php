<?php 

/* Which users on enwiki are flagged with accountcreator, compared to users on the tool. */

/*
mysql -h sql-s1
use enwiki_p;
select g.ug_user, n.user_name from user_groups g inner join user_ids n on g.ug_user=n.user_id where ug_group = "accountcreator";
*/

require_once('config.inc.php');
require_once('functions.php');

// check to see if the database is unavailable
readOnlyMessage(); //main db
ifWikiDbDisabledDie(); //antispoofdb

displayheader();

global $tsSQLlink, $asSQLlink, $toolserver_database, $antispoof_db;
list($tsSQLlink, $asSQLlink) = getDBconnections();
@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting TS database.");
@ mysql_select_db($antispoof_db, $asSQLlink) or sqlerror(mysql_error(),"Error selecting MW database.");

$query = 'select g.ug_user, n.user_name from user_groups g inner join user_ids n on g.ug_user=n.user_id where ug_group = "accountcreator";';
$results = mysql_query($query,$asSQLlink) or die();
echo "<h2>Enwiki accountcreators without tool access</h2><table cellspacing=\"0\">";
echo "<tr><th>en.wiki User ID</th><th>en.wiki Username</th><th /><th /><th /></tr>";//<th>acc. User ID</th><th>acc. Username</th><th>acc. Access level</th></tr>";
$currentreq = 0;
while($row = mysql_fetch_assoc($results))
{
	$query='SELECT user_id, user_name, user_level FROM `acc_user` WHERE user_onwikiname = "'.$row['user_name'].'" AND (`user_level` = "Admin" OR `user_level` = "User") LIMIT 1;';
	$accresult = mysql_query($query, $tsSQLlink);
	if($accresult){
		$accrow = mysql_fetch_assoc($accresult);
	} else { $accrow = array('user_name' => '--', 'user_id' => '--', 'user_level' => '--'); }
	if( $accrow['user_id'] == '')
	{
		$accrow = array('user_name' => '--', 'user_id' => '--', 'user_level' => '--');
	}
	if( ($accrow['user_name'] == '--') ||  ($row['user_name']=='--')){
		$currentreq++;
		echo '<tr';
		if ($currentreq % 2 == 0) {
			echo ' class="alternate">';
		} else {
			echo '>';
		}
		echo "<td>".$row['ug_user']."</td><td><a href=\"http://en.wikipedia.org/wiki/User:".$row['user_name']."\">".$row['user_name']."</a></td><td><a href=\"http://en.wikipedia.org/wiki/User_talk:".$row['user_name']."\">talk</a></td><td><a href=\"http://en.wikipedia.org/wiki/Special:Contributions/".$row['user_name']."\">contribs</a></td><td><a href=\"http://en.wikipedia.org/wiki/Special:UserRights/".$row['user_name']."\">rights</a></td>"; //<td>".$accrow['user_id']."</td><td>".$accrow['user_name']."</td><td>".$accrow['user_level']."</td></tr>";
	}
}



echo "</table>";

displayfooter();
?>
