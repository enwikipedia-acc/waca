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
**OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
**Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
**FunPika    ( http://en.wikipedia.org/wiki/User:FunPika )   **
**************************************************************/

// this script returns the current length of the welcome bot's job queue

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