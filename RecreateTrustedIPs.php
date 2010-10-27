<?php
if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} // Web clients die.
ini_set('display_errors', 1);
require_once 'config.inc.php';

$htmlfile = file_get_contents('http://www.wikimedia.org/trusted-xff.html');
$matches = array();
$machfound = preg_match_all('(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)', $htmlfile, $matches);
$insertquery = 'INSERT INTO `acc_trustedips` (`trustedips_ipaddr`) VALUES ';
if ($matchfound) {
	foreach ($matches as $match) {
		$ip = $match[0];
		$insertquery .= "('$ip'), ";
	}
} else {
	die('ERROR: No IPs found on trusted XFF page.');
}
$insertquery = substr($insertquery, 0, -2) . ';';

mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
@ mysql_select_db($toolserver_database) or die(mysql_error());
mysql_query('TRUNCATE TABLE `acc_trustedips`;');
mysql_query($insertquery);
mysql_close();

echo "The trusted IPs table has been recreated.";
?>
