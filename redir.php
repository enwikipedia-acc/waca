<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

$toolList = array(
    'tparis-pcount'      => '//tools.wmflabs.org/supercount/index.php?user=%DATA%&project=en.wikipedia',
    'guc'                => '//tools.wmflabs.org/guc/?by=date&user=%DATA%',
    'oq-whois'           => 'https://whois.domaintools.com/%DATA%',
    'tl-whois'           => 'https://tools.wmflabs.org/whois/gateway.py?lookup=true&ip=%DATA%',
    'honeypot'           => 'https://www.projecthoneypot.org/ip_%DATA%',
    'stopforumspam'      => 'https://www.stopforumspam.com/ipcheck/%DATA%',
    'google'             => 'https://www.google.com/search?q=%DATA%',
    'domain'             => 'http://%DATA%/',
    'rangefinder'        => 'https://tools.wmflabs.org/rangeblockfinder/?ip=%DATA%',
    'ipcheck'            => 'https://ipcheck.toolforge.org/index.php?ip=%DATA%',
    'bgpview'            => 'https://bgpview.io/ip/%DATA%'
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
        $data = urlencode($data);
    }

    echo '<script>window.location.href=' . json_encode(str_replace("%DATA%", $data, $toolList[$tool])) . '</script>';
}
else {
    header("Location: " . $_SERVER["REQUEST_URI"] . "&round2=true");
}
