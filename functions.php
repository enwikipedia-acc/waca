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
global $tsurl;
global $dontUseWikiDb;

if (!defined("ACC")) {
	die();
} // Invalid entry point

require_once 'queryBrowser.php';
require_once 'LogClass.php';
include_once 'AntiSpoof.php';
require_once 'includes/internalInterface.php';
require_once 'includes/session.php';
require_once 'includes/request.php';
require_once 'includes/authutils.php';

// Initialize the class objects.
$internalInterface = new internalInterface();
$session = new session();

function formatForBot( $data ) {
	global $ircBotCommunicationKey;
	$pData[0] = encryptMessage( $data, $ircBotCommunicationKey );
	$pData[1] = $data;
	$sData = serialize( $pData );
	return $sData;
}

function encryptMessage( $text, $key ) {
	$keylen = strlen($key);

	if( $keylen % 2 == 0 ) {
		$power = ord( $key[$keylen / 2] ) + $keylen;
	}
	else {
		$power = ord( $key[($keylen / 2) + 0.5] ) + $keylen;
	}

	$textlen = strlen( $text );
	while( $textlen < 64 ) {
		$text .= $text;
		$textlen = strlen( $text );
	}

	$newtext = null;
	for( $i = 0; $i < 64; $i++ ) {
		$pow = pow( ord( $text[$i] ), $power );
		$pow = str_replace( array( '+', '.', 'E' ), '', $pow );
		$toadd = dechex( substr($pow, -2) );
		while( strlen( $toadd ) < 2 ) {
			$toadd .= 'f';
		}
		if( strlen( $toadd ) > 2 ) $toadd = substr($toadd, -2);
		$newtext .= $toadd;
	}

	return $newtext;
}

function _utf8_decode($string) {
	/*
	 * Improved utd8_decode() function
	 */
	$tmp = $string;
	$count = 0;
	while (mb_detect_encoding($tmp) == "UTF-8") {
		$tmp = utf8_decode($tmp);
		$count++;
	}

	for ($i = 0; $i < $count -1; $i++) {
		$string = utf8_decode($string);
	}
	return $string;
}

function getSpoofs( $username ) {
	global $dontUseWikiDb;
	if( !$dontUseWikiDb ) {
		global $toolserver_username;
		global $toolserver_password;
		global $antispoof_host;
		global $antispoof_db;
		global $antispoof_table;
		global $antispoof_password;
		$spooflink = mysql_pconnect($antispoof_host, $toolserver_username, $antispoof_password);
		$link = mysql_select_db($antispoof_db, $spooflink);
		if( !$link ) {
			sqlerror(mysql_error(),"Error selecting database.");
		}
		$return = AntiSpoof::checkUnicodeString( $username );
		if($return[0] == 'OK' ) {
			$sanitized = sanitize($return[1]);
			$query = "SELECT su_name FROM ".$antispoof_table." WHERE su_normalized = '$sanitized';";
			$result = mysql_query($query, $spooflink);
			if(!$result) sqlerror("ERROR: No result returned. - ".mysql_error(),"Database error.");
			$numSpoof = 0;
			$reSpoofs = array();
			while ( list( $su_name ) = mysql_fetch_row( $result ) ) {
				if( isset( $su_name ) ) { $numSpoof++; }
				array_push( $reSpoofs, $su_name );
			}
			mysql_close( $spooflink );
			if( $numSpoof == 0 ) {
				return( FALSE );
			} else {
				return( $reSpoofs );
			}
		} else {
			return ( $return[1] );
		}
	} else { return "This function is currently disabled."; }
}

function sanitize($what) {
	/*
	 * Shortcut to mysql_real_escape_string
	 */
	global $tsSQLlink;
	$what = mysql_real_escape_string($what,$tsSQLlink);
	$what = htmlentities($what,ENT_COMPAT,'UTF-8');
	return ($what);
}

function upcsum($id) {
	/*
	 * Updates the entries checksum (on each load of that entry, to prevent dupes)
	 */
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	$link = mysql_select_db($toolserver_database);
	if( !$link ) {
	 sqlerror(mysql_error(),"Error selecting database.");
	}
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$id';";
	$result = mysql_query($query);
	if (!$result)
	sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$pend = mysql_fetch_assoc($result);
	$hash = md5($pend['pend_id'] . $pend['pend_name'] . $pend['pend_email'] . microtime());
	$query = "UPDATE acc_pend SET pend_checksum = '$hash' WHERE pend_id = '$id';";
	$result = mysql_query($query);
}

function csvalid($id, $sum) {
	/*
	 * Checks to make sure the entries checksum is still valid
	 */
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	$link = mysql_select_db($toolserver_database);
	if( !$link ) {
		sqlerror(mysql_error(),"Error selecting database.");
	}
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$id';";
	$result = mysql_query($query);
	if (!$result)
	sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$pend = mysql_fetch_assoc($result);
	if (!isset($pend['pend_checksum'])) {
		upcsum($id);
		return (1);
	}
	if ($pend['pend_checksum'] == $sum) {
		return (1);
	} else {
		return (0);
	}
}

function sendemail($messageno, $target, $id) {
	/*
	 * Send a "close pend ticket" email to the end user. (created, taken, etc...)
	 */
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or sqlerror(mysql_error(),"Error selecting database.");
	$template = EmailTemplate::getById($messageno, gGetDb());
	$mailtxt = $template->getText();
	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	
	// Get the closing user's Email signature and append it to the Email.
	$sid = sanitize($_SESSION['user']);
	$query = "SELECT user_emailsig FROM acc_user WHERE user_name = '$sid'";
	$result = mysql_query($query);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if($row['user_emailsig'] != "") {
		$emailsig = html_entity_decode($row['user_emailsig'], ENT_QUOTES, "UTF-8");
		mail($target, "RE: [ACC #$id] English Wikipedia Account Request", $mailtxt . "\n\n" . $emailsig, $headers);
	}
	else {
		mail($target, "RE: [ACC #$id] English Wikipedia Account Request", $mailtxt, $headers);
	}
}

function isProtected($requestid)
{
	global $protectReservedRequests;

	if(! $protectReservedRequests ) return false;

	$reservedTo = isReserved($requestid);

	if($reservedTo)
	{
		if($reservedTo == $_SESSION['userID']) {
			return false;
		} else {
			return true;
		}
	} else {
		return false;
	}
	
	return false;
}

/**
 * Checks to see if a request is marked as reserved by a user
 * Returns uid if reserved, false if not
 */
function isReserved($requestid)
{
	if (!preg_match('/^[0-9]*$/',$requestid)) {
		die('Invalid Input.'); // TODO: make this a pretty error message
	}

	$rqid = sanitize($requestid);
	$query = "SELECT pend_reserved FROM acc_pend WHERE pend_id = $rqid;";
	$result = mysql_query($query);
	if (!$result) {
		die("Error determining reserved status of request. Check the request id.");
	}
	$row = mysql_fetch_assoc($result);
	return isReservedWithRow($row);
}

function isReservedWithRow($row) {
	if(isset($row['pend_reserved']) && $row['pend_reserved'] != 0) { 
		return $row['pend_reserved'];
	} else {return false;}
}

/**
 * Show the login page
 * @param (ignored)
 * @param (ignored)
 * @todo re-implement parameters
 */
function showlogin($action=null, $params=null) 
{
    global $smarty;
    
	// Check whether there are any errors.
    $errorbartext = "";
	if (isset($_GET['error'])) {
		if ($_GET['error']=='authfail') 
        {
            $errorbartext = BootstrapSkin::displayAlertBox("Username and/or password incorrect. Please try again.", "alert-error","Auth failure",true,false,true);
		} 
        elseif ($_GET['error']=='noid') 
        {
            $errorbartext = BootstrapSkin::displayAlertBox("User account is not identified. Please email accounts-enwiki-l@lists.wikimedia.org if you believe this is in error.", "alert-error","Auth failure",true,false,true);
		} 
        elseif ($_GET['error']=='newacct') 
        {
            $errorbartext = BootstrapSkin::displayAlertBox("I'm sorry, but, your account has not been approved by a site administrator yet. Please stand by.", "alert-info","Account pending",true,false,true);
		}
	}
    $smarty->assign("errorbar", $errorbartext);   
    
    $smarty->display("login.tpl");
}

function getdevs() {
	global $regdevlist;
	$newdevlist = array_reverse($regdevlist);
	$temp = $newdevlist['0'];
	unset ($newdevlist['0']);
	foreach ($newdevlist as $dev) {
		$devs .= "<a href=\"http://en.wikipedia.org/wiki/User talk:" . $dev['1'] . "\">" . $dev['0'] . "</a>, ";
	}
	$devs .= "<a href=\"http://en.wikipedia.org/wiki/User talk:" . $temp['1'] . "\">" . $temp['0'] . "</a>";
	return $devs;
}

function defaultpage() {
	global $availableRequestStates, $defaultRequestStateKey, $requestLimitShowOnly, $enableEmailConfirm;
    
    $database = gGetDb();
    
    $requestSectionData = array();
    
    if ($enableEmailConfirm == 1) 
    {		
        $query = "SELECT * FROM request WHERE status = :type AND emailconfirm = 'Confirmed' LIMIT :lim;";
        $totalquery = "SELECT COUNT(*) FROM request WHERE status = :type AND emailconfirm = 'Confirmed';";
    } 
    else 
    {
        $query = "SELECT * FROM request WHERE status = :type LIMIT :lim;";
        $totalquery = "SELECT COUNT(*) FROM request WHERE status = :type;";
    }
    
    $statement = $database->prepare($query);
    $statement->bindParam(":lim", $requestLimitShowOnly, PDO::PARAM_INT);
    
    $totalRequestsStatement = $database->prepare($totalquery);
            
	// list requests in each section
	foreach($availableRequestStates as $type => $v) 
    {
        $statement->bindParam(":type", $type);
        $statement->execute();
        
        $requests = $statement->fetchAll(PDO::FETCH_CLASS, "Request");
        foreach($requests as $req)
        {
            $req->setDatabase($database);   
        }

        $totalRequestsStatement->bindParam(":type", $type);
        $totalRequestsStatement->execute();
        $totalRequests = $totalRequestsStatement->fetchColumn();
        $totalRequestsStatement->closeCursor();
        
        $requestSectionData[$v['header']] = array("requests" => $requests, "total" => $totalRequests);
    }
    
    global $smarty;
    $smarty->assign("requestLimitShowOnly", $requestLimitShowOnly);
	
    $query = "SELECT pend_id, pend_name, pend_checksum, log_time FROM acc_pend JOIN acc_log ON pend_id = log_pend WHERE log_action LIKE 'Closed%' ORDER BY log_time DESC LIMIT 5;";
    $statement = $database->prepare($query);
    $statement->execute();
    
    $last5result = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    $smarty->assign("lastFive", $last5result);
    $smarty->assign("requestSectionData", $requestSectionData);
    $html = $smarty->fetch("mainpage/mainpage.tpl");
    
	return $html;
}

/**
 * If the Wiki/antispoof database is marked as disabled, then die.
 */
function ifWikiDbDisabledDie() {
	global $dontUseWikiDb;
	if( $dontUseWikiDb ){
		echo "Apologies, this command requires access to the wiki database, which is currently unavailable.";
		die();
	}
}

function sqlerror ($sql_error, $generic_error="Query failed.") {
	/*
	 * Show the user an error
	 * depending on $enableSQLError.
	 */
	global $enableSQLError;
	if ($enableSQLError) {
		die($sql_error);
	} else {
		die($generic_error);
	}
}

function array_search_recursive($needle, $haystack, $path=array())
{
	foreach($haystack as $id => $val)
	{
		$path2=$path;
		$path2[] = $id;

		if($val === $needle)
		return $path2;
		else if(is_array($val))
		if($ret = array_search_recursive($needle, $val, $path2))
		return $ret;
	}
	return false;
}

function isOnWhitelist($user)
{
	$apir = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&prop=revisions&titles=Wikipedia:Request_an_account/Whitelist&rvprop=content&format=php");
	$apir = unserialize($apir);
	$apir = $apir['query']['pages'];

	foreach($apir as $r) {
		$text = $r['revisions']['0']['*'];
	}

	if( preg_match( '/\*\[\[User:'.$user.'\]\]/', $text ) ) {
		return true;
	}
	return false;
}

require_once('zoompage.php');

function displayPreview($wikicode) {
	$parseresult = unserialize(file_get_contents('http://en.wikipedia.org/w/api.php?action=parse&format=php&text='.urlencode($wikicode)));
	$out = "<br />\n<h3>Preview</h3>\n<div style=\"border: 2px dashed rgb(26, 79, 133);\">\n<div style=\"margin: 20px;\">";
	$out .= $parseresult['parse']['text']['*'];
	$out .= '</div></div>';
	return $out;
}

/**
 * A simple implementation of a bubble sort on a multidimensional array.
 *
 * @param array $items A two-dimensional array, to be sorted by a date variable
 * in the 'time' field of the arrays inside the array passed.
 * @return array sorted array.
 */
function doSort(array $items)
{
	// Loop through until it's sorted
	do{
		// reset flag to false, we've not made any changes in this iteration yet
		$flag = false;
		
		// loop through the array
		for ($i = 0; $i < (count($items) - 1); $i++) {
			// are these two items out of order?
			if(strtotime($items[$i]['time']) > strtotime($items[$i + 1]['time']))
			{
				// swap them
				$swap = $items[$i];
				$items[$i] = $items[$i + 1];
				$items[$i + 1] = $swap;
				
				// set a flag to say we've modified the array this time around
				$flag = true;
			}
		}
	}
	while($flag);
	
	// return the array back to the caller
	return $items;
}

function showIPlinks($ip, $wikipediaurl, $metaurl, $rqid, &$session) {
	global $tsurl;
	
	$out = '<a class="request-src" href="'.$wikipediaurl.'wiki/User_talk:';
	$out .= $ip . '" target="_blank">Talk page</a> ';

	// IP contribs
	$out .= '| ';
	$out .= '<a class="request-src" href="'.$wikipediaurl.'wiki/Special:Contributions/';
	$out .= $ip . '" target="_blank">Local Contributions</a> ';
	
	
	
	//X's edit counter
	$out .= '| ';
	$out .= '<a class="request-src" href="'.$tsurl . "/redir.php?tool=tparis-pcount&data=" . $ip. '"';
	$out .= ' target="_blank">Deleted Edits</a> ';

	// IP global contribs
	$out .= '| ';
	$out .= '<a class="request-src" href="'. $tsurl . "/redir.php?tool=luxo-contributions&data=" . $ip. '" target="_blank">Global Contributions</a> ';
	
	// IP blocks
	$out .= '| ';
	$out .= '<a class="request-src" href="'.$wikipediaurl.'w/index.php?title=Special:Log&amp;type=block&amp;page=User:';
	$out .= $ip . '" target="_blank">Local Block Log</a> ';

	// rangeblocks
	$out .= '| ';
	$out .= '<a class="request-src" href="'.$wikipediaurl.'w/index.php?title=Special%3ABlockList&amp;ip=';
	$out .= $ip . '" target="_blank">Active Local Blocks</a> ';

	// Global blocks
	$out .= '| ';
	$out .= '<a class="request-src" href="'.$metaurl.'w/index.php?title=Special:Log&amp;type=gblblock&amp;page=User:';
	$out .= $ip . '" target="_blank">Global Block Log</a> ';

	// Global range blocks/Locally disabled Global Blocks
	$out .= '| ';
	$out .= '<a class="request-src" href="'.$wikipediaurl.'w/index.php?title=Special%3AGlobalBlockList&amp;ip=';
	$out .= $ip . '" target="_blank">Active Global Blocks</a> ';

	// IP whois
	$out .= '| ';
	$out .= '<a class="request-src" href="' . $tsurl . "/redir.php?tool=oq-whois&data=" . $ip . '" target="_blank">Whois</a> ';

	// IP geolocate
	$out .= '| ';
	$out .= '<a class="request-src" href="' . $tsurl . "/redir.php?tool=ipinfodb-locator&data=" . $ip . '" target="_blank">Geolocate</a> ';

	// Abuse Filter
	$out .= '| ';
	$out .= '<a class="request-src" href="' . $wikipediaurl . 'w/index.php?title=Special:AbuseLog&amp;wpSearchUser=' . $ip . '" target="_blank">Abuse Filter Log</a> ';
	 
	if( $session->isCheckuser($_SESSION['user']) ) {
	// CheckUser links
	 $out .= '| ';
	 $out .= '<a class="request-src" href="' . $wikipediaurl . 'w/index.php?title=Special:CheckUser&ip=' . $ip . '&reason=%5B%5BWP:ACC%5D%5D%20request%20%23' . $rqid . '" target="_blank">CheckUser</a> ';
	}
	 

	return $out;
	
}

$trustedipcache = array();
function isXffTrusted($ip) {
	global $tsSQL, $squidIpList, $trustedipcache;
	
	if(in_array($ip, $squidIpList)) return true;
	if(array_key_exists($ip, $trustedipcache)) return $trustedipcache[$ip];
	
	$query = "SELECT * FROM `acc_trustedips` WHERE `trustedips_ipaddr` = '$ip';";
	$result = $tsSQL->query($query);
	if (!$result) {
		$tsSQL->showError("Query failed: $query ERROR: ".$tsSQL->getError(),"ERROR: Database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
	}
	if (mysql_num_rows($result)) {
		$trustedipcache[$ip] = true;
		return true;
	}
	else {
		$trustedipcache[$ip] = false;
		return false;
	}
}

function getTrustedClientIP($dbip, $dbproxyip)
{
	$clientIpAddr = $dbip;
	if($dbproxyip)
	{
		$ipList = explode(",", $dbproxyip);
		$ipList[] = $clientIpAddr;
		$ipList = array_reverse($ipList);
		
		foreach($ipList as $ipnumber => $ip){
			if(isXffTrusted(trim($ip)) && $ipnumber < (count($ipList) - 1)) continue;
			
			$clientIpAddr = $ip;
			break;
		}
	}
	
	return $clientIpAddr;
}

function explodeCidr( $range ) {
	$ip_arr = explode( '/' , $range );

	if( ! isset( $ip_arr[1] ) ) {
		return array( $range );
	}
	
	$blow = ( 
		str_pad( decbin( ip2long( $ip_arr[0] ) ), 32, "0", STR_PAD_LEFT) &
		str_pad( str_pad( "", $ip_arr[1], "1" ), 32, "0" ) 
		);
	$bhigh = ($blow | str_pad( str_pad( "", $ip_arr[1], "0" ), 32, "1" ) );

	$list = array();

	for( $x = bindec( $blow ); $x <= bindec( $bhigh ); $x++ ) {
		$list[] = long2ip( $x );
	}

	return $list;
}

/**
 * Takes an array( "low" => "high ) values, and returns true if $needle is in at least one of them.
 */
function ipInRange( $haystack, $ip ) {
	$needle = ip2long($ip);

	foreach( $haystack as $low => $high ) {
		if( ip2long($low) <= $needle && ip2long($high) >= $needle ) {
			return true;
		}
	}
	return false;
}

function welcomerbotRenderSig($creator, $sig) {
	$signature = html_entity_decode($sig) . ' ~~~~~';
	if (!preg_match("/((\[\[[ ]*(w:)?[ ]*(en:)?)|(\{\{subst:))[ ]*User[ ]*:[ ]*".$creator."[ ]*(\]\]|\||\}\}|\/)/i", $signature)) {
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
    
    if ($secs <= 10) 
    {
        $output = "just now";
        $pluralise = false;
    }
    elseif ($secs > 10 && $secs < $minuteCut) 
    {
        $output = round($secs/$second) . " second";
    }
    elseif ($secs >= $minuteCut && $secs < $hourCut) 
    {
        $output = round($secs/$minute) . " minute";
    }
    elseif ($secs >= $hourCut && $secs < $dayCut) 
    {
        $output = round($secs/$hour) . " hour";
    }
    elseif ($secs >= $dayCut && $secs < $weekCut) 
    {
        $output = round($secs/$day) . " day";
    }
    elseif ($secs >= $weekCut && $secs < $month) 
    {
        $output = round($secs/$week) . " week";
    }
    elseif ($secs >= $month && $secs < $year) 
    {
        $output = round($secs/$month) . " month";
    }
    elseif ($secs >= $year && $secs < $year * 10) 
    {
        $output = round($secs/$year) . " year";
    }
    else
    { 
        $output = "a long time ago";
        $pluralise = false;
    }
    
    if ($pluralise)
    {
        $output = (substr($output,0,2) <> "1 ") ? $output . "s ago" : $output . " ago";
    }

    return $output;
}