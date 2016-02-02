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

require_once 'includes/session.php';

// Initialize the class objects.
$session = new session();

/**
 * Send a "close pend ticket" email to the end user. (created, taken, etc...)
 * @deprecated
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
 * Show the login page
 *
 * @deprecated
 */
function showlogin()
{
	ob_end_clean();
	global $baseurl;
	header("Location: $baseurl/internal.php/login");
	die;
}

/**
 * @deprecated
 */
function defaultpage()
{
	ob_end_clean();
	global $baseurl;
	header("Location: $baseurl/internal.php");
	die();
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
 * @param array  $haystack
 * @return bool
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

	if (gettype($input) === 'object' && get_class($input) === DateTime::class) {
		$then = $input;
	}
	else {
		$then = new DateTime($input);
	}

    
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
