<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/

if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} //Web clients die.

//ini_set('display_errors', '1');

echo "Initialising backup script\n";

//Config
$basefile = "backup";
$dir = "/home/project/a/c/c/acc/backups";
$monthdir = $dir . "/monthly";
$dumper = "/usr/bin/mysqldump --defaults-file=~/.my.cnf p_acc_live"; //add params here if they are needed.
$gzip = "/bin/gzip"; //add params here too if needed.
$tar = "/bin/tar -cvf";

echo "Loaded configuration\n";

$arg = $argv['1'];
if( $arg == "--monthly" ) {
	echo "running monthly backups.\n";
	$dateModifier = date( "FY" );
	$cmdLine = "$tar $monthdir/mBackup-$dateModifier.tar $dir/*.sql.gz; rm $dir/*.sql.gz";
	echo "running command $cmdLine\n";
	shell_exec( $cmdLine );
	die( "done." );
}

echo "running nightly backups\n";
$dateModifier = date( "y-m-d" );
$cmdLine = "$dumper > $dir/$basefile$dateModifier.sql; $gzip $dir/$basefile$dateModifier.sql";
echo "running command $cmdLine\n";
shell_exec( $cmdLine );
echo "done.";
?>

