<?php

$toolList = array(
	'tparis-pcount' => '//tools.wmflabs.org/supercount/index.php?user=%DATA%&project=en.wikipedia',
	'luxo-contributions' => '//tools.wmflabs.org/quentinv57-tools/tools/globalcontribs.php?username=%DATA%',
	'guc' => '//tools.wmflabs.org/guc/?user=%DATA%',
	'oq-whois' => 'https://whois.domaintools.com/%DATA%',
	'tl-whois' => 'https://tools.wmflabs.org/whois/gateway.py?lookup=true&ip=%DATA%',
	'honeypot' => 'https://www.projecthoneypot.org/ip_%DATA%',
	'stopforumspam' => 'https://www.stopforumspam.com/ipcheck/%DATA%',
	'sulutil' => '//tools.wmflabs.org/quentinv57-tools/tools/sulinfo.php?showinactivity=1&showblocks=1&username=%DATA%',
	'google' => 'https://www.google.com/search?q=%DATA%',
    'rangefinder' => 'https://tools.wmflabs.org/rangeblockfinder/?ip=%DATA%'
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
	echo '<script>window.location.href=' . json_encode(str_replace("%DATA%", urlencode($_GET['data']), $toolList[$_GET['tool']])) . '</script>';
}
else {
	header("Location: " . $_SERVER["REQUEST_URI"] . "&round2=true");
}
