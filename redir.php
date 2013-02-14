<?php

$toolList = array(
	'tparis-pcount' => 'http://toolserver.org/~tparis/pcount/index.php?lang=en&wiki=wikipedia&name=%DATA%',
	'luxo-contributions' => '//toolserver.org/~luxo/contributions/contributions.php?lang=en&blocks=true&user=%DATA%',
	'oq-whois' => '//toolserver.org/~overlordq/cgi-bin/whois.cgi?lookup=%DATA%',
	'ipinfodb-locator' => 'http://www.ipinfodb.com/ip_locator.php?ip=%DATA%',
);

if(isset($_GET['round2']))
{
	echo '<script>window.location.href="'.str_replace("%DATA%", $_GET['data'], $toolList[$_GET['tool']]).'"</script>';
}
else
{
	header("Location: " . $_SERVER["REQUEST_URI"] . "&round2=true");
}
