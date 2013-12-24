<?php

$toolList = array(
	'tparis-pcount' => 'http://tools.wmflabs.org/xtools/pcount/index.php?lang=en&wiki=wikipedia&name=%DATA%',
	'luxo-contributions' => 'http://tools.wmflabs.org/guc/?user=%DATA%',
	'oq-whois' => '//toolserver.org/~overlordq/cgi-bin/whois.cgi?lookup=%DATA%',
	'sulutil' => 'http://toolserver.org/~quentinv57/tools/sulinfo.php?showinactivity=1&showblocks=1&username=%DATA%',
);

if(isset($_GET['round2']))
{
	echo '<script>window.location.href="'.str_replace("%DATA%", $_GET['data'], $toolList[$_GET['tool']]).'"</script>';
}
else
{
	header("Location: " . $_SERVER["REQUEST_URI"] . "&round2=true");
}
