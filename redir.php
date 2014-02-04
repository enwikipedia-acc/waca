<?php

$toolList = array(
	'tparis-pcount' => '//tools.wmflabs.org/xtools/pcount/index.php?lang=en&wiki=wikipedia&name=%DATA%',
	'luxo-contributions' => '//tools.wmflabs.org/quentinv57-tools/tools/globalcontribs.php?username=%DATA%',
	'oq-whois' => 'https://whois.domaintools.com/%DATA%',
	'sulutil' => '//tools.wmflabs.org/quentinv57-tools/tools/sulinfo.php?showinactivity=1&showblocks=1&username=%DATA%',
	'google' => 'https://www.google.com/search?q=%DATA%',
);

if(isset($_GET['round2']))
{
	echo '<script>window.location.href="'.str_replace("%DATA%", $_GET['data'], $toolList[$_GET['tool']]).'"</script>';
}
else
{
	header("Location: " . $_SERVER["REQUEST_URI"] . "&round2=true");
}
