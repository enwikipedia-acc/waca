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

// Get all the classes.
require_once 'config.inc.php';

//ini_set('display_errors', '1');

echo "Initialising backup script\n";

$arg = $argv['1'];
if ($arg == "--monthly") {
	echo "running monthly backups.\n";
	$dateModifier = date("FY");
	$cmdLine = "$BUtar $BUmonthdir/mBackup-$dateModifier.tar $BUdir/*.sql.gz; rm $BUdir/*.sql.gz";
	echo "running command $cmdLine\n";
	shell_exec($cmdLine);
	die("done.");
}

echo "running nightly backups\n";
$dateModifier = date("y-m-d");
$cmdLine = "$BUdumper > $BUdir/$BUbasefile$dateModifier.sql; $BUgzip $BUdir/$BUbasefile$dateModifier.sql";
echo "running command $cmdLine\n";
shell_exec($cmdLine);
echo "done.";
