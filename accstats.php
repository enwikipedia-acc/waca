<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Bootstrap is licensed under the Apache License 2.           			 **
** Smarty is licensed under the GNU Lesser General Public License 2.1    ** 
** and later.															 **
** AntiSpoof is licensed under the GNU General Public License 2 and      **
** later. 																 **
**                                                                       **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/

require_once ('config.inc.php');

if ( isset( $_SERVER['REMOTE_ADDR'] ) ) { // Invalid entry point.
	header( "Location: $baseurl/" );
	die( );
}

mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
@ mysql_select_db($toolserver_database) or print mysql_error();

$openq = "SELECT COUNT(*) FROM acc_pend WHERE pend_status = 'Open' AND pend_mailconfirm = 'Confirmed';";
$result = mysql_query($openq);
if (!$result)
	Die("ERROR: No result returned on open requests query.");
$open = mysql_fetch_assoc($result);

$adminq = "SELECT COUNT(*) FROM acc_pend WHERE pend_status = 'Admin' AND pend_mailconfirm = 'Confirmed';";
$result = mysql_query($adminq);
if (!$result)
	Die("ERROR: No result returned on account creator requests query.");
$admin = mysql_fetch_assoc($result);

$unconfirmedq = "SELECT COUNT(*) FROM acc_pend WHERE pend_mailconfirm != 'Confirmed' AND pend_mailconfirm != '';";
$result = mysql_query($unconfirmedq);
if (!$result)
	Die("ERROR: No result returned on unconfirmed requests query.");
$unconfirmed = mysql_fetch_assoc($result);

$sadminq = "SELECT COUNT(*) FROM user WHERE status = 'Admin';";
$result = mysql_query($sadminq);
if (!$result)
	Die("ERROR: No result returned on \"Site admins\" query.");
$sadmin = mysql_fetch_assoc($result);

$scheckuserq = "SELECT COUNT(*) FROM user WHERE checkuser = '1';";
$result = mysql_query($scheckuserq);
if (!$result)
	Die("ERROR: No result returned on \"Site checkusers\" query.");
$scheckuser = mysql_fetch_assoc($result);

$suserq = "SELECT COUNT(*) FROM user WHERE status = 'User';";
$result = mysql_query($suserq);
if (!$result)
	Die("ERROR: No result returned on \"Site users\" query.");
$suser = mysql_fetch_assoc($result);

$ssuspq = "SELECT COUNT(*) FROM user WHERE status = 'Suspended';";
$result = mysql_query($ssuspq);
if (!$result)
	Die("ERROR: No result returned on \"Site suspended accounts\" query.");
$ssusp = mysql_fetch_assoc($result);

$snewq = "SELECT COUNT(*) FROM user WHERE status = 'New';";
$result = mysql_query($snewq);
if (!$result)
	Die("ERROR: No result returned on \"Site users awaiting approval\" query.");
$snew = mysql_fetch_assoc($result);

$now = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1));

/* Get number of requests by queue. */
$queueout = "";
foreach ($availableRequestStates as $state) {
	$deferq = "SELECT * FROM acc_pend WHERE pend_status = '" . $state['api'] . "';";
	$result = mysql_query($deferq);
	if (!$result)
		Die("ERROR: No result returned on count requests in " . $state['deferto'] . " queue.");
	$count = mysql_num_rows($result);
	$queueout .= "Open Requests (" . $state['deferto'] . "): $count\n";
}

/* Get top 5 account creators of all time */
$topqa = "SELECT log_user, COUNT(*) FROM acc_log LEFT JOIN emailtemplate ON concat('Closed ', id) = log_action WHERE (oncreated = '1' OR log_action = 'Closed custom-y') GROUP BY log_user ORDER BY count(*) DESC LIMIT 5;";
$result = mysql_query($topqa);
if (!$result)
	Die("ERROR: No result returned on top 5 account creators query.");
$top5a = array ();
while ($topa = mysql_fetch_assoc($result))
	array_push($top5a, $topa);
$top5aout = "\nAll time top 5 account creators: (see $baseurl/statistics.php?page=TopCreators for more!)\n";
$top5aout .= "-------------------------------------------------------------\n";
foreach ($top5a as $top1a)
	$top5aout .= $top1a['log_user'] . " - " . $top1a['COUNT(*)'] . "\n";
$top5aout .= "\n";

/* Query for listing newly approved tool users */
$whosnewq = "SELECT * FROM acc_log JOIN user u ON log_pend = u.id WHERE log_action = 'Approved' AND log_time LIKE '$now%';";
$result = mysql_query($whosnewq);
if (!$result)
	Die("ERROR: No result returned on \"New ACC Users Approved today\" query.");
$whosnew = array ();
while ($wn = mysql_fetch_assoc($result)) {
	$wn_one = $wn['user_name'];
	array_push($whosnew, $wn_one);
}
$wnout = "\nNew ACC Users Approved today:\n";
$wnout .= "-------------------------------------------------------------\n";
if (count($whosnew) == 0)
	$wnout .= "None.\n";
else {
	foreach ($whosnew as $wn_one)
		$wnout .= "$wn_one\n";
}
$wnout .= "\n";

/* Top 5 account creators today */
$topq = "SELECT log_user, COUNT(*) FROM acc_log  LEFT JOIN emailtemplate ON concat('Closed ', id) = log_action WHERE (oncreated = '1' OR log_action = 'Closed custom-y') AND log_time LIKE '$now%' GROUP BY log_user ORDER BY count(*) DESC limit 5;";
$result = mysql_query($topq);
if (!$result)
	Die("ERROR: No result returned on \"Today's top 5 account creators\" query.");
$top5 = array ();
while ($top = mysql_fetch_assoc($result))
	array_push($top5, $top);
$top5out = "\nToday's top 5 account creators: (see $baseurl/statistics.php?page=TopCreators for more!)\n";
$top5out .= "-------------------------------------------------------------\n";
foreach ($top5 as $top1)
	$top5out .= $top1['log_user'] . " - " . $top1['count(*)'] . "\n";
$top5out .= "\n";

/* Get close statistics for today. */
$templateq = "SELECT id, name FROM emailtemplate WHERE active = '1';";
$result = mysql_query($templateq);
if (!$result)
	Die("ERROR: No result returned on get list of Email Templates query.");
$closeout = "";
while ($row = mysql_fetch_assoc($result)) {
	$id = $row['id'];
	$name = $row['name'];
	$closeq = "SELECT * FROM acc_log WHERE log_action = 'Closed $id' AND log_time LIKE '$now%';";
	$result2 = mysql_query($closeq);
	if (!$result2)
		Die("ERROR: No result returned on count number of $name closures.");
	$count = mysql_num_rows($result2);
	$closeout .= "Requests closed as \"$name\": $count\n";
}
// Dropped requests
$closeq = "SELECT * FROM acc_log WHERE log_action = 'Closed 0' AND log_time LIKE '$now%';";
$result = mysql_query($closeq);
if (!$result)
	Die("ERROR: No result returned on count number of dropped requests.");
$count = mysql_num_rows($result);
$closeout .= "Requests dropped: $count\n";
// Custom closed (created)
$closeq = "SELECT * FROM acc_log WHERE log_action = 'Closed custom-y' AND log_time LIKE '$now%';";
$result = mysql_query($closeq);
if (!$result)
	Die("ERROR: No result returned on count number of custom closed (created) requests.");
$count = mysql_num_rows($result);
$closeout .= "Requests custom closed (created): $count\n";
// Custom closed (not created)
$closeq = "SELECT * FROM acc_log WHERE log_action = 'Closed custom-n' AND log_time LIKE '$now%';";
$result = mysql_query($closeq);
if (!$result)
	Die("ERROR: No result returned on count number of custom closed (not created) requests.");
$count = mysql_num_rows($result);
$closeout .= "Requests custom closed (not created): $count\n";

/* Get deferral statistics for today. */
$deferout = "";
foreach ($availableRequestStates as $state) {
	$deferq = "SELECT * FROM acc_log WHERE log_action = 'Deferred to " . $state['defertolog'] . "' AND log_time LIKE '$now%';";
	$result = mysql_query($deferq);
	if (!$result)
		Die("ERROR: No result returned on count requests deferred to " . $state['deferto'] . ".");
	$count = mysql_num_rows($result);
	$deferout .= "Requests deferred to " . $state['deferto'] . ": $count\n";
}

$nopen = $open['COUNT(*)'];
$nadmin = $admin['COUNT(*)'];
$nsadmin = $sadmin['COUNT(*)'];
$nscheckuser = $scheckuser['COUNT(*)'];
$nsuser = $suser['COUNT(*)'];
$nssusp = $ssusp['COUNT(*)'];
$nsnew = $snew['COUNT(*)'];
$nunconfirmed = $unconfirmed['COUNT(*)'];

/* Put mail together */
$out = "\n";
$out .= "Tool URL is $baseurl/acc.php\n\n";
$out .= "Site Statistics as of " . date('l\, F jS Y\, \a\t h:i:s A') . "!\n";
$out .= "-------------------------------------------------------------\n";
$out .= $queueout;
$out .= "Awaiting Confirmation: $nunconfirmed\n";
$out .= "Site admins: $nsadmin\n";
$out .= "Site users: $nsuser\n";
$out .= "Site checkusers: $nscheckuser\n";
$out .= "Site suspended accounts: $nssusp\n";
$out .= "Site users awaiting approval: $nsnew\n\n";
$out .= "Todays statistics!\n";
$out .= "-------------------------------------------------------------\n";
$out .= $closeout;
$out .= $deferout;
$out .= $top5aout;
$out .= $top5out;
$out .= $wnout;
echo $out;

/* Send actual mail */
$to = 'accounts-enwiki-l@lists.wikimedia.org';
$subject = "TS ACC statistics, $now";
$message = $out;
$headers = 'From: accstats@helpmebot.org.uk' . "\n";
if( $argv[1] != "-testrun" )
	mail($to, $subject, $message, $headers);
