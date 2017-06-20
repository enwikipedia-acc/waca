<?php
/**************************************************************************
 **********      English Wikipedia Account Request Interface      **********
 ***************************************************************************
 ** Wikipedia Account Request Graphic Design by Charles Melbye,           **
 ** which is licensed under a Creative Commons                            **
 ** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
 **                                                                       **
 ** All other code are released under the Public Domain                   **
 ** by the ACC Development Team.                                          **
 **                                                                       **
 ** See CREDITS for the list of developers.                               **
 ***************************************************************************/

global $ACC;
global $baseurl;
global $dontUseWikiDb;

if (!defined("ACC")) {
	die();
} // Invalid entry point

require_once 'queryBrowser.php';
require_once 'includes/session.php';

// Initialize the class objects.
$session = new session();

/** Initialises the PHP Session */
function initialiseSession() {
    session_start();
}

/**
 * Send a "close pend ticket" email to the end user. (created, taken, etc...)
 */
function sendemail($messageno, $target, $id)
{
	$template = EmailTemplate::getById($messageno, gGetDb());
	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	    
	// Get the closing user's Email signature and append it to the Email.
	if (User::getCurrent()->getEmailSig() != "") {
		$emailsig = html_entity_decode(User::getCurrent()->getEmailSig(), ENT_QUOTES, "UTF-8");
		mail($target, "RE: [ACC #$id] English Wikipedia Account Request", $template->getText() . "\n\n" . $emailsig, $headers);
	}
	else {
		mail($target, "RE: [ACC #$id] English Wikipedia Account Request", $template->getText(), $headers);
	}
}

/**
 * Returns a value indicating whether the current request was issued over HTTPSs
 * @return bool true if HTTPS
 */
function isHttps()
{
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
		if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
			// Client <=> Proxy is encrypted
			return true;
		}
		else {
			// Proxy <=> Server link is encrypted, but not Client <=> Proxy.
			return false;
		}
	}

	if (isset($_SERVER['HTTPS'])) {
		if ($_SERVER['HTTPS'] === 'off') {
			// ISAPI on IIS breaks the spec. :(
			return false;
		}

		if ($_SERVER['HTTPS'] !== '') {
			// Set to a non-empty value
			return true;
		}
	}

	return false;
}

/**
 * Show the login page
 */
function showlogin()
{
	global $smarty;
    
	// Check whether there are any errors.
	$errorbartext = "";
	if (isset($_GET['error'])) {
		if ($_GET['error'] == 'authfail') {
			$errorbartext = BootstrapSkin::displayAlertBox("Username and/or password incorrect. Please try again.", "alert-error", "Auth failure", true, false, true);
		}
		elseif ($_GET['error'] == 'noid') {
			$errorbartext = BootstrapSkin::displayAlertBox("User account is not identified. Please email accounts-enwiki-l@lists.wikimedia.org if you believe this is in error.", "alert-error", "Auth failure", true, false, true);
		}
		elseif ($_GET['error'] == 'newacct') {
			$errorbartext = BootstrapSkin::displayAlertBox("I'm sorry, but, your account has not been approved by a site administrator yet. Please stand by.", "alert-info", "Account pending", true, false, true);
		}
	}
	$smarty->assign("errorbar", $errorbartext);   

	global $strictTransportSecurityExpiry;
	if ($strictTransportSecurityExpiry !== false) {
		if (isHttps()) {
			// Client can clearly use HTTPS, so let's enforce it for all connections.
			header("Strict-Transport-Security: max-age=15768000");
		}
		else {
			// This is the login form, not the request form. We need protection here.
			$path = 'https://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
			header("Location: " . $path);
		}
	}

	$smarty->display("login.tpl");
}

function defaultpage()
{
	global $availableRequestStates, $defaultRequestStateKey, $requestLimitShowOnly, $enableEmailConfirm;
    
	$database = gGetDb();
    
	$requestSectionData = array();
    
	if ($enableEmailConfirm == 1) {
		$query = "SELECT * FROM request WHERE status = :type AND emailconfirm = 'Confirmed' LIMIT :lim;";
		$totalquery = "SELECT COUNT(*) FROM request WHERE status = :type AND emailconfirm = 'Confirmed';";
	}
	else {
		$query = "SELECT * FROM request WHERE status = :type LIMIT :lim;";
		$totalquery = "SELECT COUNT(*) FROM request WHERE status = :type;";
	}
    
	$statement = $database->prepare($query);
	$statement->bindValue(":lim", $requestLimitShowOnly, PDO::PARAM_INT);
    
	$totalRequestsStatement = $database->prepare($totalquery);
            
	// list requests in each section
	foreach ($availableRequestStates as $type => $v) {
		$statement->bindValue(":type", $type);
		$statement->execute();
        
		$requests = $statement->fetchAll(PDO::FETCH_CLASS, "Request");
		foreach ($requests as $req) {
			$req->setDatabase($database);   
		}

		$totalRequestsStatement->bindValue(":type", $type);
		$totalRequestsStatement->execute();
		$totalRequests = $totalRequestsStatement->fetchColumn();
		$totalRequestsStatement->closeCursor();
        
		$requestSectionData[$v['header']] = array(
			"requests" => $requests, 
			"total" => $totalRequests, 
			"api" => $v['api'],
            "type" => $type);
	}
    
	global $smarty;
	$smarty->assign("requestLimitShowOnly", $requestLimitShowOnly);
	
	$query = <<<SQL
		SELECT request.id, request.name, request.checksum
		FROM request 
		JOIN log ON log.objectid = request.id and log.objecttype = 'Request'
		WHERE log.action LIKE 'Closed%' 
		ORDER BY log.timestamp DESC 
		LIMIT 5;
SQL;
    
	$statement = $database->prepare($query);
	$statement->execute();
    
	$last5result = $statement->fetchAll(PDO::FETCH_ASSOC);
    
	$smarty->assign("lastFive", $last5result);
	$smarty->assign("requestSectionData", $requestSectionData);
	$html = $smarty->fetch("mainpage/mainpage.tpl");
    
	return $html;
}

function array_search_recursive($needle, $haystack, $path = array())
{
	foreach ($haystack as $id => $val) {
		$path2 = $path;
		$path2[] = $id;

		if ($val === $needle) {
				return $path2;
		}
		else if (is_array($val)) {
				if ($ret = array_search_recursive($needle, $val, $path2)) {
						return $ret;
				}
		}
	}
	return false;
}

require_once('zoompage.php');

function displayPreview($wikicode)
{
	$parseresult = unserialize(file_get_contents('http://en.wikipedia.org/w/api.php?action=parse&format=php&text=' . urlencode($wikicode)));
	$out = "<br />\n<h3>Preview</h3>\n<div style=\"border: 2px dashed rgb(26, 79, 133);\">\n<div style=\"margin: 20px;\">";
	$out .= $parseresult['parse']['text']['*'];
	$out .= '</div></div>';
	return $out;
}

/**
 * Parses an XFF header and client IP to find the last trusted client IP
 * 
 * @param string $dbip The IP address the request came from
 * @param string $dbproxyip The contents of the XFF header of the request
 * @return string
 */
function getTrustedClientIP($dbip, $dbproxyip)
{
	global $xffTrustProvider;
    
	$clientIpAddr = $dbip;
	if ($dbproxyip) {
		$ipList = explode(",", $dbproxyip);
		$ipList[] = $clientIpAddr;
		$ipList = array_reverse($ipList);
		
		foreach ($ipList as $ipnumber => $ip) {
			if ($xffTrustProvider->isTrusted(trim($ip)) && $ipnumber < (count($ipList) - 1)) {
				continue;
			}
			
			$clientIpAddr = $ip;
			break;
		}
	}
	
	return $clientIpAddr;
}

/**
 * Explodes a CIDR range into an array of addresses
 * 
 * @param string $range A CIDR-format range
 * @return array An array containing every IP address in the range
 */
function explodeCidr($range)
{
	$ip_arr = explode('/', $range);

	if (!isset($ip_arr[1])) {
		return array($range);
	}
	
	$blow = ( 
		str_pad(decbin(ip2long($ip_arr[0])), 32, "0", STR_PAD_LEFT) &
		str_pad(str_pad("", $ip_arr[1], "1"), 32, "0") 
		);
	$bhigh = ($blow | str_pad(str_pad("", $ip_arr[1], "0"), 32, "1"));

	$list = array();

	$bindecBHigh = bindec($bhigh);
	for ($x = bindec($blow); $x <= $bindecBHigh; $x++) {
		$list[] = long2ip($x);
	}

	return $list;
}

/**
 * Takes an array( "low" => "high" ) values, and returns true if $needle is in at least one of them.
 * @param string $ip
 * @param array $haystack
 */
function ipInRange($haystack, $ip)
{
	$needle = ip2long($ip);

	foreach ($haystack as $low => $high) {
		if (ip2long($low) <= $needle && ip2long($high) >= $needle) {
			return true;
		}
	}
    
	return false;
}

/**
 * @return string
 */
function welcomerbotRenderSig($creator, $sig)
{
	$signature = html_entity_decode($sig) . ' ~~~~~';
	if (!preg_match("/((\[\[[ ]*(w:)?[ ]*(en:)?)|(\{\{subst:))[ ]*User[ ]*:[ ]*" . $creator . "[ ]*(\]\]|\||\}\}|\/)/i", $signature)) {
		$signature = "--[[User:$creator|$creator]] ([[User talk:$creator|talk]]) ~~~~~";
	}
	return $signature;
}

/**
 * Transforms a date string into a relative representation of the date ("2 weeks ago").
 * @param string $input A string representing a date
 * @return string
 * @example {$variable|relativedate} from Smarty
 */
function relativedate($input)
{
	$now = new DateTime();
	$then = new DateTime($input);
    
	$secs = $now->getTimestamp() - $then->getTimestamp();
    
	$second = 1;
	$minute = 60 * $second;
	$minuteCut = 60 * $second;
	$hour = 60 * $minute;
	$hourCut = 60 * $minute;
	$day = 24 * $hour;
	$dayCut = 48 * $hour;
	$week = 7 * $day;
	$weekCut = 14 * $day;
	$month = 30 * $day;
	$year = 365 * $day;
    
	$pluralise = true;
    
	if ($secs <= 10) {
		$output = "just now";
		$pluralise = false;
	}
	elseif ($secs > 10 && $secs < $minuteCut) {
		$output = round($secs / $second) . " second";
	}
	elseif ($secs >= $minuteCut && $secs < $hourCut) {
		$output = round($secs / $minute) . " minute";
	}
	elseif ($secs >= $hourCut && $secs < $dayCut) {
		$output = round($secs / $hour) . " hour";
	}
	elseif ($secs >= $dayCut && $secs < $weekCut) {
		$output = round($secs / $day) . " day";
	}
	elseif ($secs >= $weekCut && $secs < $month) {
		$output = round($secs / $week) . " week";
	}
	elseif ($secs >= $month && $secs < $year) {
		$output = round($secs / $month) . " month";
	}
	elseif ($secs >= $year && $secs < $year * 10) {
		$output = round($secs / $year) . " year";
	}
	else {
		$output = "a long time ago";
		$pluralise = false;
	}
    
	if ($pluralise) {
		$output = (substr($output, 0, 2) <> "1 ") ? $output . "s ago" : $output . " ago";
	}

	return $output;
}

/**
 * Summary of reattachOAuthAccount
 * @param User $user 
 * @throws TransactionException 
 */
function reattachOAuthAccount(User $user)
{
	global $oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal;

	try {
		// Get a request token for OAuth
		$util = new OAuthUtility($oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal);
		$requestToken = $util->getRequestToken();

		// save the request token for later
		$user->setOAuthRequestToken($requestToken->key);
		$user->setOAuthRequestSecret($requestToken->secret);
		$user->save();
            
		$redirectUrl = $util->getAuthoriseUrl($requestToken);
            
		header("Location: {$redirectUrl}");
		die();
	}
	catch (Exception $ex) {
		throw new TransactionException($ex->getMessage(), "Connection to Wikipedia failed.", "alert-error", 0, $ex);
	}     
}

/**
 * Generates the JavaScript source for XSS-safe typeahead autocompletion for usernames.  This output is expected to be
 * passed as the $tailscript argument to \BootstrapSkin::displayInternalFooter().
 *
 * @param $users string[] Array of usernames as strings
 * @return string
 */
function getTypeaheadSource($users)
{
	$userList = "";
	foreach ($users as $v) {
		$userList .= "\"" . htmlentities($v) . "\", ";
	}
	$userList = "[" . rtrim($userList, ", ") . "]";
	$tailscript = <<<JS
$('.username-typeahead').typeahead({
	source: {$userList}
});
JS;
	return $tailscript;
}
