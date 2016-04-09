<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

$toolList = array(
	'tparis-pcount'      => '//tools.wmflabs.org/supercount/index.php?user=%DATA%&project=en.wikipedia',
	'luxo-contributions' => '//tools.wmflabs.org/quentinv57-tools/tools/globalcontribs.php?username=%DATA%',
	'guc'                => '//tools.wmflabs.org/guc/?user=%DATA%',
	'oq-whois'           => 'https://whois.domaintools.com/%DATA%',
	'sulutil'            => '//tools.wmflabs.org/quentinv57-tools/tools/sulinfo.php?showinactivity=1&showblocks=1&username=%DATA%',
	'google'             => 'https://www.google.com/search?q=%DATA%',
	'link'               => 'http://%DATA%/',
);

if (isset($_GET['round2'])) {
	$data = $_GET['data'];
	$tool = $_GET['tool'];

	if ($tool === 'link') {
		// quick security check - if you want to exploit something, you better be sure your exploit resolves via dns.
		// this is not intended to catch everything, just as a quick sanity check.
		if (gethostbyname($data) == $data) {
			echo 'Error resolving hostname, it doesn\'t look like this domain exists.';
			die();
		}
	}

	echo '<script>window.location.href="' . str_replace("%DATA%", $data, $toolList[$tool]) . '"</script>';
}
else {
	header("Location: " . $_SERVER["REQUEST_URI"] . "&round2=true");
}
