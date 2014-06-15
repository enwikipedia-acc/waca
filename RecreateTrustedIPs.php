<?php
if (isset($_SERVER['REQUEST_METHOD'])) {
	die();
} // Web clients die.
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
require_once 'config.inc.php';
require_once 'includes/PdoDatabase.php';
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

$ip = array_unique($ip);

$database = gGetDb();

echo "Executing transaction...\n";
$database->transactionally(function() use ($ip, $database)
{
    $database->exec("DELETE FROM acc_trustedips;");
    
    $insert = $database->prepare("INSERT INTO acc_trustedips (trustedips_ipaddr) VALUES (:ip);");
    
    foreach ($ip as $i)
    {
        $insert->execute(array(":ip", $i));
    }
}