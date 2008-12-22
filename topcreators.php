<?php

require_once ('config.inc.php');
require_once('functions.php');

mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
@ mysql_select_db($toolserver_database) or print mysql_error();

$now = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")));

$topqa = "select log_user,count(*) from acc_log where log_action = 'Closed 1' group by log_user ORDER BY count(*) DESC;";
$result = mysql_query($topqa);
if (!$result)
	Die("ERROR: No result returned.6");
$top5a = array ();
while ($topa = mysql_fetch_assoc($result)) {
	array_push($top5a, $topa);
}
$top5aout = "<h2>All time top account creators</h2>";
$top5aout .= "<table><tr><th># Created</th><th>Username</th></tr>";
foreach ($top5a as $top1a) {
	$userq = "SELECT user_id FROM acc_user WHERE user_name = \"".$top1a['log_user']."\";";
	$userr = mysql_query($userq);
	$user = mysql_fetch_assoc($userr);
	
	$top5aout .= "<tr><td>".$top1a['count(*)']."</td><td><a href=\"users.php?viewuser=".$user['user_id']."\">".$top1a['log_user'] . "</a></td></tr>";
}
$top5aout .= "</table>";


$topq = "select log_user,count(*) from acc_log where log_time like '$now%' and log_action = 'Closed 1' group by log_user ORDER BY count(*) DESC;";
$result = mysql_query($topq);
if (!$result)
	Die("ERROR: No result returned.6");
$top5 = array ();
while ($top = mysql_fetch_assoc($result)) {
	array_push($top5, $top);
}

//Get today's top 5
$top5out = "<h2>Today's account creators</h2>";
$top5out .= "<table><tr><th># Created</th><th>Username</th></tr>";
foreach ($top5 as $top1) {
	$userq = "SELECT user_id FROM acc_user WHERE user_name = \"".$top1['log_user']."\";";
	$userr = mysql_query($userq);
	$user = mysql_fetch_assoc($userr);
	$top5out .= "<tr><td>".$top1['count(*)']."</td><td><a href=\"users.php?viewuser=".$user['user_id']."\">".$top1['log_user'] . "</a></td></tr>";
}
$top5out .= "</table>";

displayheader();
echo $top5aout;
echo $top5out;
echo "<br />";
displayfooter();
?>