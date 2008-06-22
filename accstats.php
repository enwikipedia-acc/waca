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
**                                                           **
**************************************************************/

require_once('config.inc.php');
if( $_SERVER['REMOTE_ADDR'] != "") {
        header("Location: $tsurl/");
        die();
}

mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
@mysql_select_db($toolserver_database) or print mysql_error();

$openq = "select COUNT(*) from acc_pend where pend_status = 'Open';";
$result = mysql_query($openq);
if(!$result) Die("ERROR: No result returned.1");
$open = mysql_fetch_assoc($result);  

$adminq = "select COUNT(*) from acc_pend where pend_status = 'Admin';";  
$result = mysql_query($adminq);
if(!$result) Die("ERROR: No result returned.2");
$admin = mysql_fetch_assoc($result);  

$sadminq = "select COUNT(*) from acc_user where user_level = 'Admin';";  
$result = mysql_query($sadminq);
if(!$result) Die("ERROR: No result returned.3");
$sadmin = mysql_fetch_assoc($result);  

$suserq = "select COUNT(*) from acc_user where user_level = 'User';";  
$result = mysql_query($suserq);
if(!$result) Die("ERROR: No result returned.4");
$suser = mysql_fetch_assoc($result);  

$ssuspq = "select COUNT(*) from acc_user where user_level = 'Suspended';";  
$result = mysql_query($ssuspq);
if(!$result) Die("ERROR: No result returned.5");
$ssusp = mysql_fetch_assoc($result);  

$snewq = "select COUNT(*) from acc_user where user_level = 'New';";  
$result = mysql_query($snewq);
if(!$result) Die("ERROR: No result returned.6");
$snew = mysql_fetch_assoc($result);  

$now = date("Y-m-d",mktime(0,0,0,date(m),date("d")-1));


//Get top 5 account creators of all time
$topqa = "select log_user,count(*) from acc_log where log_action = 'Closed 1' group by log_user ORDER BY count(*) DESC limit 5;";
$result = mysql_query($topqa);
if(!$result) Die("ERROR: No result returned.6");
$top5a = array();
while ($topa = mysql_fetch_assoc($result)) {
        array_push($top5a, $topa);
}
$top5aout .= "\nAll time top 5 account creators:\n";
$top5aout .= "-------------------------------------------------------------\n";
foreach ($top5a as $top1a) {
        $top5aout .= "$top1a[log_user] - " . $top1a['count(*)'] . "\n";
}
$topa5out .= "\n";
$whosnewq = "select * from acc_log JOIN acc_user on log_pend = user_id where log_action = 'Approved' AND log_time LIKE '$now%';";
echo "$whosnewq\n";
$result = mysql_query($whosnewq);
if(!$result) Die("ERROR: No result returned.6.1");
$whosnew = array();
while ($wn = mysql_fetch_assoc($result)) {
	$wn_one = $wn[user_name];
	array_push($whosnew, $wn_one);
}

//Get today's new users
$wnout .= "\nNew ACC Users Approved today:\n";
$wnout .= "-------------------------------------------------------------\n";
if (count($whosnew) == 0) {
	$wnout .= "None.\n";
}
else {
	foreach ($whosnew as $wn_one) {
		$wnout .= "$wn_one\n";
	}
}
$wnout .= "\n";

$topq = "select log_user,count(*) from acc_log where log_time like '$now%' and log_action = 'Closed 1' group by log_user ORDER BY count(*) DESC limit 5;";
$result = mysql_query($topq);
if(!$result) Die("ERROR: No result returned.6");
$top5 = array();
while ($top = mysql_fetch_assoc($result)) {
	array_push($top5, $top);
}

//Get today's top 5
$top5out .= "\nTodays top 5 account creators:\n";
$top5out .= "-------------------------------------------------------------\n";
foreach ($top5 as $top1) {
	$top5out .= "$top1[log_user] - " . $top1['count(*)'] . "\n";
}
$top5out .= "\n";

//Process log for stats
$logq = "select * from acc_log AS A
	JOIN acc_pend AS B ON log_pend = pend_id
	where log_time RLIKE '^$now.*' AND
	log_action RLIKE '^(Closed.*|Deferred.*|Blacklist.*)';";
$result = mysql_query($logq);
if(!$result) Die("ERROR: No result returned.7");
$dropped = 0;
$created = 0;
$toosimilar = 0;
$taken = 0;
$usernamevio = 0;
$technical = 0;
$dadmins = 0;
$dusers = 0;
while($log = mysql_fetch_assoc($result)) {
	switch ($log[log_action]) {
	case "Closed 0": //Dropped
	    $dropped++;
	    break;
	case "Closed 1": //Created
	    $created++;
	    break;
	case "Closed 2": //Too similar
	    $toosimilar++; 
	    break;
	case "Closed 3": //Taken
	    $taken++;
	    break;
	case "Closed 4": //Username vio
	    $usernamevio++;
	    break;
	case "Closed 5": //Techinically impossible
	    $technical++;
	    break;
	case "Deferred to admins":
	    $dadmins++;
	    break;
	case "Deferred to users":
	    $dusers++;
	    break;
	case "Blacklist Hit":
	    $blusers++;
	    break;
	}
} 
$nopen = $open['COUNT(*)'];
$nadmin = $admin['COUNT(*)'];
$nsadmin = $sadmin['COUNT(*)'];
$nsuser = $suser['COUNT(*)'];
$nssusp = $ssusp['COUNT(*)'];
$nsnew = $snew['COUNT(*)'];
$bltd = $blcount['COUNT(*)'];

//Put mail together
$out = "\n";
$out .= "Tool URL is $tsurl/acc.php\n";
$out .= "PLEASE, register if you have not already!\n\n";
$out .= "Site Statistics as of ".date('l\, F jS Y\, \a\t h:i:s A')."!\n";
$out .= "-------------------------------------------------------------\n";
$out .= "Open Requests: $nopen\n";
$out .= "Open Requests (admin required): $nadmin\n";
$out .= "Site admins: $nsadmin\n";
$out .= "Site users: $nsuser\n";
$out .= "Site suspended accounts: $nssusp\n";
$out .= "Site users awaiting approval: $nsnew\n\n";
$out .= "Todays statistics!\n";
$out .= "-------------------------------------------------------------\n";
$out .= "Requests dropped because of blacklisting: $bltd\n";
$out .= "Account requests dropped: $dropped\n";
$out .= "Accounts successfully created: $created\n";
$out .= "Accounts not created (Too similar): $toosimilar\n";
$out .= "Accounts not created (Taken): $taken\n";
$out .= "Accounts not created (Username vio): $usernamevio\n";
$out .= "Accounts not created (Technically impossible): $technical\n";
$out .= "Requests deferred to admins: $dadmins\n";
$out .= "Requests deferred back to users or reopened: $dusers\n";
$out .= $top5aout;
$out .= $top5out;
$out .= $wnout;
echo $out;

//Send actual mail
$to      = 'accounts-enwiki-l@lists.wikimedia.org';
$subject = "TS ACC statistics, $now";
$message = $out;
$headers = 'From: sxwiki@gmail.com' . "\n";

mail($to, $subject, $message, $headers);
?>
