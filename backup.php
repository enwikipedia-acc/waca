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
**                                                           **
**************************************************************/

if ($ACC != "1") {
    header("Location: $tsurl/");
    die();
} //Re-route, if you're a web client.

//Config
$basefile = "backup";
$dir = "/projects/acc/accbak";
$monthdir = "/projects/acc/accbak/monthly";
$dumper = "/opt/mysql/bin/mysqldump p_acc"; //add params here if they are needed.
$gzip = "/usr/bin/gzip"; //add params here too if needed.
$tar = "/usr/bin/tar -cvf";


$arg = $argv['1'];
if( $arg == "--monthly" ) {
	$dateModifier = date( "FY" );
	$cmdLine = "$tar $monthdir/mBackup-$dateModifier.tar $dir/*.sql.gz; rm $dir/*.sql.gz";
	shell_exec( $cmdLine );
	die( );
}
$dateModifier = date( "mdy" );
$cmdLine = "$dumper > $dir/$basefile$dateModifier.sql; $gzip $dir/$basefile$dateModifier.sql";
shell_exec( $cmdLine );
?>

