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

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client.

require_once 'queryBrowser.php';
require_once 'LogClass.php';
require_once 'includes/messages.php';
include_once 'AntiSpoof.php';
require_once 'includes/internalInterface.php';
require_once 'includes/session.php';
require_once 'includes/request.php';
require_once 'includes/authutils.php';
require_once 'autolink.php';

// Initialize the class objects.
$messages = new messages();
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

function sanitise($what) { return sanitize($what); }
function sanitize($what) {
	/*
	 * Shortcut to mysql_real_escape_string
	 */
	global $tsSQLlink;
	$what = mysql_real_escape_string($what,$tsSQLlink);
	$what = htmlentities($what,ENT_COMPAT,'UTF-8');
	return ($what);
}

function xss ($string) {
	return htmlentities($string,ENT_QUOTES,'UTF-8');
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
	$messageno = sanitize($messageno);
	$query = "SELECT * FROM acc_emails WHERE mail_id = '$messageno';";
	$result = mysql_query($query);
	if (!$result)
	sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$row = mysql_fetch_assoc($result);
	$mailtxt = $row['mail_text'];
	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	mail($target, "RE: [ACC #$id] English Wikipedia Account Request", $mailtxt, $headers);
}

function listrequests($type, $hideip, $correcthash) {
	/*
	 * List requests, at Zoom, and, on the main page
	 */
	global $toolserver_database, $tsSQLlink, $tsurl;
	global $secure;
	global $enableEmailConfirm;
	global $session;
	global $availableRequestStates;
	
	global $requestLimitThreshold, $requestLimitShowOnly;
	$totalRequests = 0;
	
	if($secure != 1) { die("Not logged in"); }
	@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting database.");

	if ($enableEmailConfirm == 1) {
		if (array_key_exists($type, $availableRequestStates)) {
			
			$totalRequestsQ = mysql_query("SELECT COUNT(*) FROM acc_pend WHERE pend_status = '$type' AND pend_mailconfirm = 'Confirmed';");
			$totalRequestsR = mysql_fetch_assoc( $totalRequestsQ );
			$totalRequests = $totalRequestsR["COUNT(*)"];
			
			if( $totalRequests > $requestLimitThreshold ) {
				$query = "SELECT * FROM acc_pend WHERE pend_status = '$type' AND pend_mailconfirm = 'Confirmed' LIMIT $requestLimitShowOnly;";
			} else {
				$query = "SELECT * FROM acc_pend WHERE pend_status = '$type' AND pend_mailconfirm = 'Confirmed';";
			}
		} else {
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$type';";
		}
	} else {
		if (array_key_exists($type, $availableRequestStates)) {
			$query = "SELECT * FROM acc_pend WHERE pend_status = '$type';";
		} else {
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$type';";
		}
	}

	$result = mysql_query($query);
	if (!$result)
	sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");

    $tablestart = "";
	if( $totalRequests > $requestLimitThreshold ) {
		$tablestart .= "<p><span class=\"warning\">Miser mode: not all requests are shown for speed. </span>Only $requestLimitShowOnly of $totalRequests are shown here.</p>";
    }
 
    $tablestart .= '<table class="table table-striped"><thead><tr><th><span class="hidden-phone">#</span></th><th><!-- zoom --></th><th><!-- comment --></th><th><span class="visible-desktop">Email address</span><span class="visible-tablet">Email and IP</span><span class="visible-phone">Request details</span></th><th><span class="visible-desktop">IP address</span></th><th><span class="hidden-phone">Username</span></th><th><!-- ban --></th><th><!-- reserve status --></th><th><!--reserve button--></th></tr></thead><tbody>';

	$tableend = "</tbody></table>\n";
	$reqlist = '';
	$currentreq = 0;
	
	$sid = sanitize($_SESSION['user']);
	$query4 = "SELECT * FROM acc_user WHERE user_name = '$sid';";
	$result4 = mysql_query($query4, $tsSQLlink);
	if (!$result4) {
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	}
	$row4 = mysql_fetch_assoc($result4);
	
    global $smarty;
    
	while ( $row = mysql_fetch_assoc( $result ) ) {
		$currentreq += 1;
		$smarty->assign( "rownum" , $currentreq );
        
        
        $uname = $row['pend_name'];
		$rid = $row['pend_id'];
        
        $smarty->assign( "rid", $rid );
        $smarty->assign("name", $uname);
		
		$data = mysql_query("SELECT COUNT(*) as num FROM acc_cmt where pend_id = '" . $rid . "';"); // indexed
		$commentcountrow = mysql_fetch_assoc($data);
		$commentcount=$commentcountrow['num'];
		
        $hascomments = ($row['pend_cmt'] != ""  || $commentcount != 0);
        $smarty->assign("hascmt", $hascomments);
        
		
		$clientIpAddr = getTrustedClientIP($row['pend_ip'], $row['pend_proxyip']);
        
        $smarty->assign("ip", $clientIpAddr);
        
        /// disabled for performance
        
//		$query2 = "SELECT COUNT(*) AS `count` FROM `acc_pend` WHERE (`pend_ip` = '" . mysql_real_escape_string($clientIpAddr,$tsSQLlink) . "' OR `pend_proxyip` LIKE '%" . mysql_real_escape_string($clientIpAddr,$tsSQLlink) . "%') AND `pend_mailconfirm` = 'Confirmed';";
//		$result2 = mysql_query($query2); // TODO: OPTIMISE ME! I TAKE 20s TO EXECUTE!
//		if (!$result2) {
//			sqlerror("Query failed: $query2 ERROR: " . mysql_error(),"Database query error.");
//		}
//		$otheripreqs = mysql_fetch_assoc($result2);
//		$otheripreqs["count"]--;
        $otheripreqs = array("count" => "-1");		
        
        //////////////////////////////////

        $numOtherIpRequests = $otheripreqs["count"];
        $smarty->assign("numip" , $numOtherIpRequests);
        
        $query3 = "SELECT COUNT(*) AS `count` FROM `acc_pend` WHERE `pend_email` = '" . mysql_real_escape_string($row['pend_email'],$tsSQLlink) . "' AND `pend_id` != '" . mysql_real_escape_string($row['pend_id'],$tsSQLlink) . "' AND `pend_mailconfirm` = 'Confirmed';";
		$result3 = mysql_query($query3);
		if (!$result3) {
			sqlerror("Query failed: $query3 ERROR: " . mysql_error(),"Database query error.");
		}
		$otheremailreqs = mysql_fetch_assoc($result3);
		$numOtherEmailRequests = $otheremailreqs['count'];
        $smarty->assign("nummail", $numOtherEmailRequests);
        $mailaddr = $row['pend_email'];
        $smarty->assign("mail", $mailaddr);
        
		$showdata = ($hideip == FALSE || $correcthash == TRUE || $session->hasright($_SESSION['user'], 'Admin') || $session->isCheckuser($_SESSION['user']) );
        $smarty->assign("showdata", $showdata);
        $canban = ( $session->hasright($_SESSION['user'], 'Admin') || $session->isCheckuser($_SESSION['user']) );
        $smarty->assign("canban", $canban);
	
		$reserveByUser = isReservedWithRow($row);

        $smartyreserved = false;
        $smartyyoureserved = false;
        if($reserveByUser != 0)
        {
            $smartyreserved = $session->getUsernameFromUid($reserveByUser);
            if( $reserveByUser == $_SESSION['userID'] ){
               $smartyyoureserved = true;
            }
        }
        
        $smarty->assign("reserved", $smartyreserved);  
        $smarty->assign("youreserved", $smartyyoureserved);
        $canbreak = ( $session->hasright($_SESSION['user'], 'Admin') || $session->isCheckuser($_SESSION['user']) );
        $smarty->assign("canbreak", $canbreak);
        
        
		$reqlist .= $smarty->fetch("request-entry.tpl");
	}
	if( $currentreq == 0 ) {
		return( "<i>No requests at this time</i>" );
	} else {
		return ($tablestart . $reqlist . $tableend);
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
function showlogin($action=null, $params=null) {
    global $smarty;
    
    
	// Check whether there are any errors.
    $errorbartext = "";
	if (isset($_GET['error'])) {
		if ($_GET['error']=='authfail') {
            $errorbartext = BootstrapSkin::displayAlertBox("Username and/or password incorrect. Please try again.", "alert-error","Auth failure",true,false,true);
		} elseif ($_GET['error']=='noid') {
            $errorbartext = BootstrapSkin::displayAlertBox("User account is not identified. Please email accounts-enwiki-l@lists.wikimedia.org if you believe this is in error.", "alert-error","Auth failure",true,false,true);
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
	global $tsSQLlink, $toolserver_database, $skin, $tsurl, $availableRequestStates, $defaultRequestStateKey;
	@mysql_select_db( $toolserver_database, $tsSQLlink) or sqlerror(mysql_error,"Could not select db");
	$html = '<h1>Create an account!</h1>';

	// list requests in each section
	foreach($availableRequestStates as $k => $v) {
		$html .= "<h2>".$v['header']."</h2>";
		$html .= listrequests($k, TRUE, FALSE);
	}	
	
	$html .= "<h2>Last 5 Closed requests</h2><a name='closed'></a><span id=\"closed\"/>\n";
	$query = "SELECT pend_id, pend_name, pend_checksum FROM acc_pend JOIN acc_log ON pend_id = log_pend WHERE log_action LIKE 'Closed%' ORDER BY log_time DESC LIMIT 5;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
	sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$html .= "<ol>\n";
	$currentrow = 0;
	while ( list( $pend_id, $pend_name, $pend_checksum ) = mysql_fetch_row( $result ) ) {
		$out = '<li>';
		$out .= "<a class=\"btn btn-small btn-info\" href=\"$tsurl/acc.php?action=zoom&amp;id=" . $pend_id . "\">Zoom</a><a class=\"btn btn-warning btn-small\" href=\"$tsurl/acc.php?action=defer&amp;id=" . $pend_id . "&amp;sum=" . $pend_checksum . "&amp;target=$defaultRequestStateKey\">Reset</a> <a href=\"http://en.wikipedia.org/wiki/User:" . $pend_name . "\">" . _utf8_decode($pend_name) . "</a></li>";
		$html .= $out;
	}
	$html .= "</ol>\n";
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

function zoomPage($id,$urlhash)
{
	global $tsSQLlink, $session, $skin, $tsurl, $messages, $availableRequestStates, $dontUseWikiDb, $internalInterface;

	$out = "";
	$gid = $internalInterface->checkreqid($id);
	$urlhash = sanitize($urlhash);
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['pend_mailconfirm'] != 'Confirmed' && $row['pend_mailconfirm'] != "" && !isset($_GET['ecoverride'])) {
		$out .= $skin->displayRequestMsg("Email has not yet been confirmed for this request, so it can not yet be closed or viewed.");
		return $out;
	}
	$out .= "<h2>Details for Request #" . $id . ":</h2>";
	$thisip = getTrustedClientIP($row['pend_ip'], $row['pend_proxyip']);
	$thisid = $row['pend_id'];
	$thisemail = $row['pend_email'];
	if ($row['pend_date'] == "0000-00-00 00:00:00") {
		$row['pend_date'] = "Date Unknown";
	}
	$sUser = $row['pend_name'];

	//#region setup whether data is viewable or not
	
	// build the sql fragment of possible open states
	$statesSqlFragment = " ";
	foreach($availableRequestStates as $k => $v){
		$statesSqlFragment .= "pend_status = '".sanitize($k)."' OR ";
	}
	$statesSqlFragment = rtrim($statesSqlFragment, " OR");
	
	$sessionuser = $_SESSION['userID'];
	$query = "SELECT * FROM acc_pend WHERE pend_email = '" . 
				mysql_real_escape_string($thisemail, $tsSQLlink) . 
				"' AND pend_reserved = '" . 
				mysql_real_escape_string($sessionuser, $tsSQLlink) . 
				"' AND pend_mailconfirm = 'Confirmed' AND ( ".$statesSqlFragment." );";

	$result = mysql_query($query, $tsSQLlink);
	if (!$result) {
		Die("Query failed: $query ERROR: " . mysql_error());
	}
	$hideemail = TRUE;
	if (mysql_num_rows($result) > 0) {
		$hideemail = FALSE;
	}

	$sessionuser = $_SESSION['userID'];
	$query2 = "SELECT * FROM acc_pend WHERE (pend_ip = '" . 
			mysql_real_escape_string($thisip, $tsSQLlink) . 
			"' OR pend_proxyip LIKE '%" .
			mysql_real_escape_string($thisip, $tsSQLlink) . 
			"%') AND pend_reserved = '" .
			mysql_real_escape_string($sessionuser, $tsSQLlink) . 
			"' AND pend_mailconfirm = 'Confirmed' AND ( ".$statesSqlFragment." );";

	$result2 = mysql_query($query2, $tsSQLlink);
	
	if (!$result2) {
		Die("Query failed: $query2 ERROR: " . mysql_error());
	}
	
	$hideip = TRUE;
	
	if (mysql_num_rows($result2) > 0) {
		$hideip = FALSE;
	}
	
	if( $hideip == FALSE || $hideemail == FALSE ) {
		$hideinfo = FALSE;
	} else {
		$hideinfo = TRUE;
	}
	
	//#endregion
	
	if ($row['pend_status'] == "Closed") {
		$hash = md5($thisid. $thisemail . $thisip . microtime()); //If the request is closed, change the hash based on microseconds similar to the checksums.
	} else {
		$hash = md5($thisid . $thisemail . $thisip);
	}
	if ($hash == $urlhash) {
		$correcthash = TRUE;
	}
	else {
		$correcthash = FALSE;
	}
	$requesttable = listrequests($thisid, $hideinfo, $correcthash);
	$out .= $requesttable;

	//Show the links for things like IP contributions/blocks.
	$sid = sanitize($_SESSION['user']);
	$query3 = "SELECT * FROM acc_user WHERE user_name = '$sid';";
	$result3 = mysql_query($query3, $tsSQLlink);
	if (!$result3)
	sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$row3 = mysql_fetch_assoc($result3);
	if ( $row3['user_secure'] > 0 ) {
		$wikipediaurl = "https://en.wikipedia.org/";
		$metaurl = "https://meta.wikimedia.org/";
	} else {
		$wikipediaurl = "http://en.wikipedia.org/";
		$metaurl = "http://meta.wikimedia.org/";
	}
	if ($hideinfo == FALSE || $correcthash == TRUE || $session->hasright($_SESSION['user'], 'Admin') || $session->isCheckuser($_SESSION['user']) ) {
		if ($row['pend_proxyip']) {
			$out .= '<br /><i>This request came from '.$row['pend_ip'].', stating it was forwarded for '.$row['pend_proxyip'].'. The IP address which Wikipedia will see is the first "untrusted" IP address in the list below. Links are shown for all addresses starting from where the chain becomes untrusted. IPs past the first untrusted address are not trusted to be correct. Please see the Guide for more details.</i>';

			$out .= '<p><strong>Forwarded IP addresses:</strong><table>';
			
			$tablerownum = 0;
			
			$proxies = explode(",", $row['pend_proxyip']);
			$proxies[] = $row['pend_ip'];
			
			$origin = $proxies[0];
			
			$proxies = array_reverse($proxies);
			$trust = true;
			$lasttrust = true;
			foreach($proxies as $proxynum => $p) {
				$p2 = trim($p);

				$trusted = isXffTrusted($p2);				
				$lasttrust = $trust;
				$trust = $trust & $trusted & ($proxynum < count($proxies) - 1);
				
				$entry = "<tr ". ( $tablerownum == 1 ? 'class="alternate"' : "" ).">";
				$tablerownum = ++$tablerownum % 2;
				$entry .= ( ( $origin != $p2 ) ? 
					(	$trust ? "<td style=\"color:grey;\">(trusted)</td>"
						: ($trusted ? "<td style=\"color:orange;\">(via untrusted)</td>" : "<td style=\"color:red;\">(untrusted)</td>" )
					)
					: (	$lasttrust ? "<td>(origin)</td>"
						: ("<td style=\"color:red;\">(origin untrusted)</td>" ) )
					);
					
				global $rfc1918ips;
					
				$iprdns = @ gethostbyaddr($p2);
				$ipisprivate = ipInRange($rfc1918ips, $p2);
				
				if( $iprdns == $p2 ) {
					if( $ipisprivate ) {
						$iprdns = "<i><a style=\"color:grey;\" href=\"http://en.wikipedia.org/wiki/Private_network\">Non-routable address</a></i>";
					} else {
						$iprdns = "<i>(no rdns available)</i>";
					}
				} else if( $iprdns === false ) {
                    $iprdnsfailed = true;
					$iprdns = "<i>(unable to determine address)</i>";
				} else {
					if( $ipisprivate ) {
						$iprdns = "<i><a style=\"color:grey;\" href=\"http://en.wikipedia.org/wiki/Private_network\">Non-routable address</a></i>";
					} else {
						$iprdns = "RDNS: $iprdns";
					}
				}
				$entry .= "<td style=\"padding:3px\">$p2<br /><span style=\"color:grey;\">" . $iprdns . "</span></td><td>";
				if( ( ! $trust ) && ( ! $ipisprivate ) && ( $iprdns!=="<i>(unable to determine address)</i>" ) ) {
                    $entry .= showIPlinks($p2, $wikipediaurl, $metaurl, $row['pend_id'], $session);
				}
				$entry .= "</td></tr>";
				
				
				$out .= $entry;
			}
			

			
			$out .= "</table>";
		}
		else {
			$out .= '<p><b>IP Address links:</b> ';
			$out .= showIPlinks($row['pend_ip'], $wikipediaurl, $metaurl, $row['pend_id'], $session);
		}
	}

	$userurl = urlencode(html_entity_decode($sUser));
	$userurl = str_replace("%26amp%3B", "%26", $userurl);
	
	
	$out .= '<p><b>Username links:</b> <a class="request-req" href="'.$wikipediaurl.'w/index.php?title=User:';
	$out .= $userurl . '" target="_blank">User page</a> | ';

	// 	Creation log
	$out .= '<a class="request-req" href="'.$wikipediaurl.'w/index.php?title=Special:Log&amp;type=newusers&amp;user=&amp;page=User:';
	$out .= $userurl . '" target="_blank">Creation log</a> | ';

	// 	SUL link
	$out .= '<a class="request-req" href="http://toolserver.org/~quentinv57/tools/sulinfo.php?showinactivity=1&showblocks=1&username=';
	$out .= $userurl. '" target="_blank">SUL</a> ( ';

    $out .= '<a class="request-req" href="http://toolserver.org/~hersfold/newfakeSULutil.php?username=';
    $out .= $userurl. '" target="_blank">alt</a> | ';
    
    //Show Special:CentralAuth link due to bug 35792 <https://bugzilla.wikimedia.org/show_bug.cgi?id=35792>
    $out .= '<a class="request-req" href="'.$wikipediaurl.'w/index.php?title=Special%3ACentralAuth&target=';
    $out .= $userurl.'" target="_blank">Special:CentralAuth</a> ) | ';

	// 	User list
	$out .= '<a class="request-req" href="'.$wikipediaurl.'w/index.php?title=Special%3AListUsers&amp;username=';
	$out .= $userurl . '&amp;group=&amp;limit=1" target="_blank">Username list</a> | ';
	
	//Search Wikipedia mainspace for the username.  See bug ACC-253 on Toolserver JIRA at https://jira.toolserver.org/browse/ACC-253
	$out .= '<a class="request=req" href="'.$wikipediaurl.'w/index.php?title=Special%3ASearch&profile=advanced&search=';
	$out .= $userurl . '&fulltext=Search&ns0=1&redirs=1&profile=advanced" target="_blank">Wikipedia mainspace search</a> | ';
	
	//TODO: add an api query to display editcount and blocks if we can't access the s1 cluster -- MM 09/04/11
	
	// Google
	$out .= '<a class="request-req" href="http://www.google.com/search?q=';
	$out .= preg_replace("/_/","+",$userurl) . '" target="_blank">Google search</a></p>';


	global $protectReservedRequests;
	if(! isProtected($row['pend_id']) && isReserved($row['pend_id']))
	{
		//Hide create user link because it contains the E-Mail address.
		if ($hideip == FALSE ||  $correcthash == TRUE || $session->hasright($_SESSION['user'], 'Admin') || $session->isCheckuser($_SESSION['user']) ) { 
			// Create user link
			$out .= '<p><b>Create account link:</b> <a class="request-req-create" href="'.$wikipediaurl.'w/index.php?title=Special:UserLogin/signup&amp;wpName=';
			$out .= $userurl . '&amp;wpEmail=' . urlencode($row['pend_email']) . '&amp;uselang=en-acc&amp;wpReason='.urlencode("Requested account at [[WP:ACC]], request #" . $row['pend_id']).'&amp;wpCreateaccountMail=true" target="_blank">Create!</a></p>';
		}
	}
		
	$out.="<p><b>Actions:</b> ";
	$type = $row['pend_status'];
	$checksum = $row['pend_checksum'];
	$pendid = $row['pend_id'];

	if(! isProtected($row['pend_id']) && isReserved($row['pend_id']))
	{
		// Done
		$out .= '<a class="request-done" href="' . $tsurl . '/acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=1&amp;sum=' . $row['pend_checksum'] . '"><strong>Created!</strong></a>';

		// Similar
		$out .= ' | <a class="request-done" href="' . $tsurl . '/acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=2&amp;sum=' . $row['pend_checksum'] . '">Similar</a>';

		// Taken
		$out .= ' | <a class="request-done" href="' . $tsurl . '/acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=3&amp;sum=' . $row['pend_checksum'] . '">Taken</a>';

		// SUL Taken
		$out .= ' | <a class="request-done" href="' . $tsurl . '/acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=26&amp;sum=' . $row['pend_checksum'] . '">SUL Taken</a>';

		// UPolicy
		$out .= ' | <a class="request-done" href="' . $tsurl . '/acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=4&amp;sum=' . $row['pend_checksum'] . '">UPolicy</a>';

		// Invalid
		$out .= ' | <a class="request-done" href="' . $tsurl . '/acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=5&amp;sum=' . $row['pend_checksum'] . '">Invalid</a>';

		// Email reset notification
		$out .= ' | <a class="request-done" href="' . $tsurl . '/acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=30&amp;sum=' . $row['pend_checksum'] . '">Password Reset</a>';

		// Custom
		$out .= ' | <a class="request-done" href="' . $tsurl . '/acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=custom&amp;sum=' . $row['pend_checksum'] . '">Custom</a>';
		
		// Drop
		$out .= ' | <a class="request-done" href="' . $tsurl . '/acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=0&amp;sum=' . $row['pend_checksum'] . '">Drop</a>' . "\n";
			
		if (!isset ($target)) {
			$target = "zoom";
		}
		$out .= deferlinks($type,$checksum,$pendid);
	}
	else
	{
		if(isProtected($row['pend_id'])) {
			$out .= 'This request is reserved';
		}
		else {
			$out .= 'This request is not reserved';
			if ($type != "Closed")
				$out .= ' | <a class="request-done" href="' . $tsurl . '/acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=0&amp;sum=' . $row['pend_checksum'] . '">Drop</a>' . "\n";				
			$out .= deferlinks($type,$checksum,$pendid);
		}
	}
	
	$cmtlen = strlen(trim($row['pend_cmt']));
	$request_comment = "";
	if ($cmtlen != 0) {
		$request_comment = autolink($row['pend_cmt']);
	}

	global $tsurl;

	$reservingUser = isReserved($thisid);
	if( $reservingUser != 0 )
	{
		$out .= "<h3>This request is currently being handled by " . $session->getUsernameFromUid($reservingUser) ."</h3>";
	}
	if ($reservingUser == $_SESSION['userID'] && $row['pend_status'] != "Closed") {
		$out .= '<p><b>URL to allow other users to see IP/Email:</b> <a href="' . $tsurl . '/acc.php?action=zoom&amp;id=' . $thisid . '&amp;hash=' . $hash . '">' . $tsurl . '/acc.php?action=zoom&amp;id=' . $thisid . '&amp;hash=' . $hash . '</a></p>';
	}

	global $allowViewingOfUseragent;
	if($allowViewingOfUseragent)
	{
		global $session, $suser;
		if($session->isCheckuser($_SESSION['user']))
		{
			$out .= "<h3>User agent: \"" . $row['pend_useragent'] . "\"</h3>";
		}
	}
	$out .= '<p><b>Date request made:</b> ' . $row['pend_date'] . '</p>';
	$request_date = $row['pend_date'];
	
	$request = new accRequest();
	if($request->isblacklisted($sUser))
		$out .= '<p><b>Requested username is blacklisted.</b></p>';
	
	$out2 = "<h2>Possibly conflicting usernames</h2>\n";
	$spoofs = getSpoofs( $sUser );

	// Display message if there is no conflicting usernames.
	// This part would not be displayed as soon as an account was created.
	// This is because then the username would have a spoof, ie himself.
	if( !$spoofs ) {
		$out2 .= "<i>None detected</i><br />\n";
	}

	// Checks whether there is an array of spoofs.
	elseif ( !is_array($spoofs) ) {
		$out2 .= "<h3 style='color: red'>$spoofs</h3>\n";
	}

	// Display details for the different conflicting usernames.
	else {
		$out2 .= "<ul>\n";
		foreach( $spoofs as $oSpoof ) {
			// Wouldnt work for requests where there are conflicting names.
			// The conflicting names would be tested again the created username.
			if ( $oSpoof == $sUser ) {
				$out .= "<h3>Note: This account has already been created</h3>";
				continue;
			}

			$oS = urlencode($oSpoof);

			// Show the Wikipedia Userpage of the conflicting users.
			$posc1 = '<a href="'.$wikipediaurl.'w/index.php?title=User:';
			$posc1 .= $oS . '" target="_blank">' . $oSpoof . '</a> ';

			// Show the contributions of the conflicting users.
			$posc2 = '<a href="'.$wikipediaurl.'w/index.php?title=Special:Contributions/';
			$posc2 .= $oS . '" target="_blank">contribs</a> ';

			// Show the logs of the conflicting users.
			$posc3 = '<a href="'.$wikipediaurl.'w/index.php?title=Special%3ALog&amp;type=&amp;user=&amp;page=User%3A';
			$posc3 .= $oS . '" target="_blank">Logs</a> ';

			// Open the SUL of the conflicting users.
			$posc4 = '<a href="http://toolserver.org/~quentinv57/tools/sulinfo.php?username=';
			$posc4 .= $oS . '" target="_blank">SUL</a> ';
			
			// Open the SUL of the conflicting users.
			$posc4 .= '(<a href="http://toolserver.org/~quentinv57/tools/sulinfo.php?showinactivity=1&showblocks=1&username=';
			$posc4 .= $oS . '" target="_blank">SUL-ib</a> | ';

			$posc4 .= '<a href="http://toolserver.org/~hersfold/newfakeSULutil.php?username=';
			$posc4 .= $oS . '" target="_blank">alt</a> | ';
			
			//Show Special:CentralAuth link due to bug 35792 <https://bugzilla.wikimedia.org/show_bug.cgi?id=35792>
			$posc4 .= '<a href="'.$wikipediaurl.'w/index.php?title=Special%3ACentralAuth&target=';
			$posc4 .= $oS.'" target="_blank">Special:CentralAuth</a>)';
			
			// Password reset links
			$posc5 = '<a href="'.$wikipediaurl.'wiki/Special:PasswordReset?wpUsername=';
			$posc5 .= $oS . '" target="_blank">Send Password reset</a> ';
			
			// tp counter
			$posc6 = '<a href="http://toolserver.org/~tparis/pcount/index.php?lang=en&wiki=wikipedia&name=';
			$posc6 .= $oS . '" target="_blank">Count</a> ';

			// Adds all the variables together for one line.
			$out2 .= "<li>" . $posc1 . "( " . $posc2 . " | " . $posc3 . " | " . $posc4 . " | " . $posc5 . " | " . $posc6 . " )</li>\n";
		}
		$out2 .= "</ul>\n";
	}
	$out .= $out2;

	if ($urlhash != "") {
		$out .= "<h2>Logs for this request:<small> (<a href='$tsurl/acc.php?action=comment&amp;id=$gid&amp;hash=$urlhash'>new comment</a>)</small></h2>";
	} else {
		$out .= "<h2>Logs for this request:<small> (<a href='$tsurl/acc.php?action=comment&amp;id=$gid'>new comment</a>)</small></h2>";
	}
	
	$loggerclass = new LogPage();
	$loggerclass->filterRequest=$gid;
	$logs = $loggerclass->getRequestLogs();

	
	if ($session->hasright($_SESSION['user'], 'Admin')) {
		$query = "SELECT * FROM acc_cmt JOIN acc_user ON (user_name = cmt_user) WHERE pend_id = '$gid' ORDER BY cmt_id ASC;";
	} else {
		$user = sanitise($_SESSION['user']);
		$query = "SELECT * FROM acc_cmt JOIN acc_user ON (user_name = cmt_user) WHERE pend_id = '$gid' AND (cmt_visability = 'user' OR cmt_user = '$user') ORDER BY cmt_id ASC;";
	}
	$result = mysql_query($query, $tsSQLlink);
	
	if (!$result) {
		Die("Query failed: $query ERROR: " . mysql_error());
	}
	
	while ($row = mysql_fetch_assoc($result)) {
		$logs[] = array('time'=> $row['cmt_time'], 'user'=>$row['cmt_user'], 'description' => '', 'target' => 0, 'comment' => html_entity_decode($row['cmt_comment']), 'action' => "comment", 'security' => $row['cmt_visability'], 'id' => $row['cmt_id']);
	}
	
	if($request_comment !== ""){
		$logs[] = array(
			'time'=> $request_date, 
			'user'=>$sUser, 
			'description' => '',
			'target' => 0, 
			'comment' => $request_comment, 
			'action' => "comment", 
			'security' => ''
			);
	}
	
	
	$namecache = array();
	
	if ($logs) {
		$logs = doSort($logs);
		$rownumber = 0;
		$out .= "<table>";
		foreach ($logs as $row) {
			$rownumber += 1;
			$date = $row['time'];
			$username = $row['user'];
			if(!isset($namecache[$username])) {
				$id = getUserIdFromName($username);
				$namecache['$username'] = $id;
			}
			$userid = $namecache['$username'] ;
			$action = $row['description'];
			$out .= "<tr";
			if ($rownumber % 2 == 0) {$out .= ' class="alternate"';}
			$out .= "><td style=\"white-space: nowrap\">&nbsp;";
			if ($userid !== null)
				$out .= "<a href='$tsurl/statistics.php?page=Users&amp;user=$userid'>$username</a>";
			else
				$out .= $username;
			if($row['action'] == "comment"){
				$out .= "&nbsp;</td><td>&nbsp;".$row['comment']."&nbsp;</td><td style=\"white-space: nowrap\">&nbsp;$date&nbsp;</td>";
			
				global $enableCommentEditing;
				if($enableCommentEditing && ($session->hasright($_SESSION['user'], 'Admin') || $_SESSION['user'] == $row['user']) && isset($row['id'])) {
					$out .= "<td><a href=\"$tsurl/acc.php?action=ec&amp;id=".$row['id']."\">Edit</a></td>";
				}
			} elseif($row['action'] == "Closed custom-n" ||$row['action'] == "Closed custom-y"  ) {
				$out .= "&nbsp;</td><td><em>&nbsp;$action:&nbsp;</em><br />".str_replace("\n", '<br />', xss($row['comment']))."</td><td style=\"white-space: nowrap\">&nbsp;$date&nbsp;</td>";
			} else {
				
				foreach($availableRequestStates as $deferState){
					$action=str_replace("deferred to ".$deferState['defertolog'],"deferred to ".$deferState['deferto'],$action);	//#35: The log text(defertolog) should not be displayed to the user, deferto is what should be displayed
				}
				$out .= "&nbsp;</td><td><em>&nbsp;$action&nbsp;</em></td><td style=\"white-space: nowrap\">&nbsp;$date&nbsp;</td>";
			}
			if (isset($row['security']) && $row['security'] == "admin") {
				$out .= "<td style=\"white-space: nowrap\">&nbsp;<font color='red'>(admin only)</font>&nbsp;</td>";
			} else {
				$out .= "";
			}
			$out .= "</tr>";
		}
		$out .= "</table>";
	} else {
		$out .= "<i>None.</i>\n";
	}
	if ($urlhash != "") {
		$out .= "<form action='$tsurl/acc.php?action=comment-quick&amp;hash=$urlhash' method='post' />";
	} else {
		$out .= "<form action='$tsurl/acc.php?action=comment-quick' method='post' />";
	}
	$out .= "<input type='hidden' name='id' value='$gid' /><input type='text' name='comment' size='75' /><input type='hidden' name='visibility' value='user' /><input type='submit' value='Quick Reply' />";

	$ipmsg = 'this ip';
	if ($hideinfo == FALSE || $session->hasright($_SESSION['user'], 'Admin') || $session->isCheckuser($_SESSION['user']))
	$ipmsg = $thisip;


	$out .= "<h2>Other requests from $ipmsg:</h2>\n";
	if ($thisip != '127.0.0.1') {
		$query = "SELECT * FROM acc_pend WHERE (pend_proxyip LIKE '%{$thisip}%' OR pend_ip = '$thisip') AND pend_id != '$thisid' AND pend_mailconfirm = 'Confirmed';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());

		$currentrow = 0;
		if (mysql_num_rows($result) != 0) {
			mysql_data_seek($result, 0);
		}
		while ($row = mysql_fetch_assoc($result)) {
			if ($currentrow == 0) { $out .= "<table cellspacing=\"0\">\n"; }
			$currentrow += 1;
			$out .= "<tr";
			if ($currentrow % 2 == 0) {$out .= ' class="alternate"';}
			$out .= "><td>". $row['pend_date'] . "</td><td><a href=\"$tsurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">" . $row['pend_name'] . "</a></td></tr>";
		}
		if ($currentrow == 0) {
			$out .= "<i>None.</i>\n";
		}
		else {$out .= "</table>\n";}
	} else {
		$out .= "<i>IP information cleared.</i>\n";
	}

	// Displayes other requests from this email.
	$emailmsg = 'this email';
	if ($hideinfo == FALSE || $session->hasright($_SESSION['user'], 'Admin') || $session->isCheckuser($_SESSION['user'])) {
		$emailmsg = $thisemail;
	}
	$out .= "<h2>Other requests from $emailmsg:</h2>\n";
	if ($thisemail != 'acc@toolserver.org') {
		$query = "SELECT * FROM acc_pend WHERE pend_email = '" . mysql_real_escape_string($thisemail, $tsSQLlink) . "' AND pend_id != '$thisid' AND pend_mailconfirm = 'Confirmed';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());

		$currentrow = 0;
		if (mysql_num_rows($result) != 0) {
			mysql_data_seek($result, 0);
		}
		while ($row = mysql_fetch_assoc($result)) {
			// Creates the table for the first time.
			if ($currentrow == 0) {
				$out .= "<table cellspacing=\"0\">\n";
			}

			$currentrow += 1;
			$out .= "<tr";
			if ($currentrow % 2 == 0) {$out .= ' class="alternate"';}
			$out .= "><td>". $row['pend_date'] . "</td><td><a href=\"$tsurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">" . $row['pend_name'] . "</a></td></tr>";
		}
		// Checks whether there were similar requests.
		if ($currentrow == 0) {
			$out .= "<i>None.</i>\n";
		}
		else {$out .= "</table>";}
	} else {
		$out .= "<i>Email information cleared.</i>\n";
	}

//Script connect for request closure abort 
    $sid = sanitize( $_SESSION['user'] );
	$query = "SELECT * FROM acc_user WHERE user_name = '$sid'";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
		if(array_key_exists('user_abortpref',$row)){
		$out.= '<script language=javascript>';
		$out.= $messages->getMessage(32);
		if($row['user_abortpref']==0){
			//Checks user preferences and accordingly runs script (see script.js)
			$out.= 'abortChecker()';
		}
		}else{
			//Run script anyways if preference does not exist
			$out.= 'abortChecker()';		
		}
		$out.= '</script>';
		
	return $out;
}

function deferlinks($type, $checksum, $pendid) {
	global $tsurl, $availableRequestStates, $defaultRequestStateKey;
	
	if(!array_key_exists($type, $availableRequestStates))
	{
		return " | <a class=\"request-done\" href=\"$tsurl/acc.php?action=defer&amp;id=$pendid&amp;sum=$checksum&amp;target=".$defaultRequestStateKey."\">Reset Request</a>";
	}
	
	$out = " | Defer to: ";
	
	foreach(array_diff_key($availableRequestStates, array($type=>$availableRequestStates[$type])) as $k => $v)
	{
		$out .= "<a class=\"request-done\" href=\"$tsurl/acc.php?action=defer&amp;id=$pendid&amp;sum=$checksum&amp;target=".$k."\">".$v['deferto']."</a> - ";
	}

	$out = rtrim($out, '- ');

	return $out;
	
}

function getToolVersion() {
	return exec("git describe --always --dirty");
}

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

function getUserIdFromName($name) {
	global $tsSQLlink;
	$res = mysql_query("SELECT user_id FROM acc_user WHERE user_name = '" . mysql_real_escape_string($name, $tsSQLlink) . "';", $tsSQLlink);
	if (!$res) {
		return null;
	}
	
	$r = mysql_fetch_assoc($res);
	return $r['user_id'];
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
?>
