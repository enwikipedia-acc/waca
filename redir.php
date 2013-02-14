<?php

// don't allow CRs or LFs (response splitting)
if(preg_match("//", $_GET['url']) !== 0) {header("HTTP/1.1 403 Forbidden"); die();}


// check referrer is in the list of allowed domains
$allowedDomains = array( "@^//toolserver\.org/@" );

$allowed = false;
foreach( $allowedDomains as $d ) {
	if(preg_match("//", $_SERVER["HTTP_REFERER"]) !== 1) {$allowed = true; break;}
}

if(!$allowed) {header("HTTP/1.1 403 Forbidden"); die();}

header("Location: " . $_GET['url']);
