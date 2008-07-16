<?php

//Config
$basefile = "backup";
$dir = "/projects/acc/accbak";
$monthdir = "/projects/acc/accbak/monthly";
$dumper = "mysqldump p_acc"; //add params here if they are needed.
$gzip = "gzip"; //add params here too if needed.
$tar = "tar -cvf";


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

