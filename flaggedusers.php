<?php 

/* Which users on enwiki are flagged with accountcreator, compared to users on the tool. */

/*
mysql -h sql-s1
use enwiki_p;
select g.ug_user, n.user_name from user_groups g inner join user_ids n on g.ug_user=n.user_id where ug_group = "accountcreator";
*/

require_once('config.inc.php');

$wikilink = mysql_connect($antispoof_host, $toolserver_username, $toolserver_password, true);
$acclink = mysql_connect($toolserver_host,$toolserver_username, $toolserver_password, true);
@mysql_select_db($antispoof_db, $wikilink);
@mysql_select_db($toolserver_database, $acclink);

$query = 'select g.ug_user, n.user_name from user_groups g inner join user_ids n on g.ug_user=n.user_id where ug_group = "accountcreator";';
$results = mysql_query($query,$wikilink) or die();

echo "<h2>List of users on enwiki with accountcreator flag</h2><table>";
echo "<tr><th>en.wiki User ID</th><th>en.wiki Username</th></tr>";
while($row = mysql_fetch_assoc($results))
{
echo "<tr><th>".$row['ug_user']."</th><td>".$row['user_name']."</td></tr>";
}



echo "</table>";
?>