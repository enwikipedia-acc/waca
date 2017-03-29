<?php

$toolList = array(
	'tparis-pcount' => '//tools.wmflabs.org/supercount/index.php?user=%DATA%&project=en.wikipedia',
	'luxo-contributions' => '//tools.wmflabs.org/quentinv57-tools/tools/globalcontribs.php?username=%DATA%',
	'guc' => '//tools.wmflabs.org/guc/?user=%DATA%',
	'oq-whois' => 'https://whois.domaintools.com/%DATA%',
	'tl-whois' => 'https://tools.wmflabs.org/whois/gateway.py?lookup=true&ip=%DATA%',
	'sulutil' => '//tools.wmflabs.org/quentinv57-tools/tools/sulinfo.php?showinactivity=1&showblocks=1&username=%DATA%',
	'google' => 'https://www.google.com/search?q=%DATA%',
	'link' => 'http://%DATA%/',
);

if(!isset($_GET['tool'])
	|| !isset($toolList[$_GET['tool']])
	|| !isset($_GET['data'])
)
{
	header("HTTP/1.1 403 Forbidden");
	return;
}

if (isset($_GET['round2'])) {
	echo '<script>window.location.href="' . str_replace("%DATA%", htmlentities($_GET['data'], ENT_COMPAT, 'UTF-8'), $toolList[$_GET['tool']]) . '"</script>';
}
else {
	header("Location: " . $_SERVER["REQUEST_URI"] . "&round2=true");
}
