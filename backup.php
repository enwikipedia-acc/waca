<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

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
