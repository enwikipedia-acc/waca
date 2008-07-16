<?php

//Config
$basefile = "backup";
$dir = "/projects/acc/accbak";
$dumper = "mysqldump p_acc"; //add params here if they are needed.
$gzip = "gzip"; //add params here too if needed.

$dateModifier = date("mdy");
$cmdLine = "$dumper > $dir/$basefile$dateModifier.sql; $gzip $dir/$basefile$dateModifier.sql";
shell_exec($cmdLine);
?>

