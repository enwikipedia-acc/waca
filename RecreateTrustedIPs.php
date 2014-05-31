<?php
if (isset($_SERVER['REQUEST_METHOD'])) {
	die();
} // Web clients die.
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
require_once 'config.inc.php';
require_once 'functions.php';

echo "Fetching file...\n";

$htmlfile = file( $xff_trusted_hosts_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

$ip = array();
$iprange = array();
$dnsdomain = array();

echo "Sorting file...\n";
foreach ( $htmlfile as $line_num => $line ) {
	// skip the comments
	if( substr( $line, 0, 1 ) === "#" ) continue;
	
	// match a regex of an CIDR range:
	$ipcidr = "@(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(?:/(?:32|3[01]|[0-2]?[0-9]))?@";
	if( preg_match( $ipcidr, $line ) === 1 ) {
		$iprange[] = $line;
		continue;
	}
	
	$ipnoncidr = "@(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(?:/(?:32|3[01]|[0-2]?[0-9]))?@";
	if( preg_match( $ipnoncidr, $line ) === 1 ) {
		$ip[] = $line;
		continue;
	}
	
	// it's probably a DNS name.
	$dnsdomain[] = $line;
}
	
echo "Exploding CIDRs...\n";
foreach ( $iprange as $r ) {
	$ips = explodeCidr($r);
	
	foreach ( $ips as $i ) {
		$ip[] = $i;
	}
}

echo "Resolving DNS...\n";
foreach ( $dnsdomain as $d ) {
	$ips = gethostbynamel( $d );
	
	if( $ips === false ) {
		echo "Invalid DNS name $d\n";
		continue;
	}
	
	foreach ( $ips as $i ) {
		$ip[] = $i;
	}
	
	
	// don't DoS
	usleep( 10000 );
}

echo "Uniq-ing array...\n";

$ip=array_unique($ip);
$count = count($ip);

echo "Prepping query for $count addresses...";


$sqlquery = 'INSERT INTO `acc_trustedips` (`trustedips_ipaddr`) VALUES ';
foreach ($ip as $i) {
	$sqlquery .= "('$i'), ";
}
$sqlquery = substr($sqlquery, 0, -2) . ';';

mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
@ mysql_select_db($toolserver_database) or die(mysql_error());

echo "Executing transaction...\n";

mysql_query("SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;");

if(mysql_query("START TRANSACTION;"))
{
	$success1 = mysql_query("DELETE FROM `acc_trustedips`;");
	if(!$success1)
		echo mysql_error()."\n";

	$success2 = mysql_query($sqlquery);
	if(!$success2)
		echo mysql_error()."\n";

	if($success1 && $success2)
	{
		mysql_query("COMMIT;");
		echo "The trusted IPs table has been recreated.\n";
	}
	else
	{
		mysql_query("ROLLBACK;");
		echo "Error in transaction.\n";
	}
}
else
	echo "Error starting transaction.\n";
	
mysql_close();
