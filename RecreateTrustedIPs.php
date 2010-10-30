<?php
if (isset($_SERVER['REQUEST_METHOD'])) {
	die();
} // Web clients die.
ini_set('display_errors', 1);
require_once 'config.inc.php';

$htmlfile = file_get_contents('http://www.wikimedia.org/trusted-xff.html');
$matches = array();
$matchfound = preg_match_all('/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/', $htmlfile, $matches);
if (!$matchfound)
	die('ERROR: No IPs found on trusted XFF page.');
$sqlquery = 'INSERT INTO `acc_trustedips` (`trustedips_ipaddr`) VALUES ';
foreach ($matches as $match) {
	$ip = $match[0];
	$sqlquery .= "('$ip'), ";
}
$sqlquery = substr($sqlquery, 0, -2) . ';';

mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
@ mysql_select_db($toolserver_database) or die(mysql_error());

mysql_query("SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;");

if(mysql_query("START TRANSACTION;"))
{
	$success = true;
	$success = mysql_query("TRUNCATE TABLE `acc_trustedips`;") && $success;
	$success = mysql_query($sqlquery) && $success;

	if($success)
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


?>
