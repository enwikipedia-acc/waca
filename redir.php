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
	'tl-whois'           => 'https://tools.wmflabs.org/whois/gateway.py?lookup=true&ip=%DATA%',
    'sulutil'            => '//tools.wmflabs.org/quentinv57-tools/tools/sulinfo.php?showinactivity=1&showblocks=1&username=%DATA%',
    'google'             => 'https://www.google.com/search?q=%DATA%',
    'domain'             => 'http://%DATA%/',
);

if (!isset($_GET['tool'])
    || !isset($toolList[$_GET['tool']])
    || !isset($_GET['data'])
) {
    header("HTTP/1.1 403 Forbidden");

    return;
}

if (isset($_GET['round2'])) {
    $data = $_GET['data'];
    $tool = $_GET['tool'];

    if ($tool === 'domain') {
        // quick security check - if you want to exploit something, you better be sure your exploit resolves via dns.
        // this is not intended to catch everything, just as a quick sanity check.
        if (gethostbyname($data) == $data) {
            echo 'Error resolving hostname, it doesn\'t look like this domain exists.';
            die();
        }
    }
    else {
        $data = htmlentities($data, ENT_COMPAT, 'UTF-8');
    }

    echo '<script>window.location.href="' . str_replace("%DATA%", $data, $toolList[$tool]) . '"</script>';
}
else {
    header("Location: " . $_SERVER["REQUEST_URI"] . "&round2=true");
}
