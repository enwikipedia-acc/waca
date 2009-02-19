<?php
require_once ('config.inc.php');
require_once('functions.php');

readOnlyMessage();

global $tsSQLlink, $asSQLlink, $toolserver_database;
list($tsSQLlink, $asSQLlink) = getDBconnections();

mysql_select_db($toolserver_database, $tsSQLlink);

$query = "SELECT COUNT(*) AS pending FROM acc_welcome WHERE welcome_status = \"Open\";";
$result = mysql_query($query, $tsSQLlink) or print mysql_error();;
$row = mysql_fetch_assoc($result) or print mysql_error();;
echo $row['pending'];