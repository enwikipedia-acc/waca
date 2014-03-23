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

if (!defined("ACC")) {
	die();
} // Invalid entry point

function zoomPage($id,$urlhash)
{
	global $tsSQLlink, $session, $availableRequestStates, $createdid;
	global $smarty, $locationProvider, $rdnsProvider;
    
    $database = gGetDb();
    $request = Request::getById($id, $database);
    if($request == false)
    {
        // Notifies the user and stops the script.
        BootstrapSkin::displayAlertBox("Could not load the requested request!", "alert-error","Error",true,false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
        
    $smarty->assign('request', $request);
    
	$urlhash = sanitize($urlhash);
    
    $smarty->assign("usernamerawunicode", html_entity_decode($request->getName()));
    
    $smarty->assign("iplocation", $locationProvider->getIpLocation($request->getTrustedIp()));
        
	$createdreason = EmailTemplate::getById($createdid, gGetDb());
	$smarty->assign("createdEmailTemplate", $createdreason);

	//#region setup whether data is viewable or not
	
	// build the sql fragment of possible open states
	$statesSqlFragment = " ";
	foreach($availableRequestStates as $k => $v){
		$statesSqlFragment .= "pend_status = '".sanitize($k)."' OR ";
	}
	$statesSqlFragment = rtrim($statesSqlFragment, " OR");
	
	$sessionuser = $_SESSION['userID'];
	$query = "SELECT * FROM acc_pend WHERE pend_email = '" . 
				mysql_real_escape_string($request->getEmail(), $tsSQLlink) . 
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
			mysql_real_escape_string($request->getTrustedIp(), $tsSQLlink) . 
			"' OR pend_proxyip LIKE '%" .
			mysql_real_escape_string($request->getTrustedIp(), $tsSQLlink) . 
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
	
	if ($request->getStatus() == "Closed") {
		$hash = md5($request->getId() . $request->getEmail() . $request->getTrustedIp() . microtime()); //If the request is closed, change the hash based on microseconds similar to the checksums.
		$smarty->assign("isclosed", true);
	} else {
		$hash = md5($request->getId() . $request->getEmail() . $request->getTrustedIp());
		$smarty->assign("isclosed", false);
	}
	$smarty->assign("hash", $hash);
	if ($hash == $urlhash) {
		$correcthash = TRUE;
	}
	else {
		$correcthash = FALSE;
	}
	
	$smarty->assign("showinfo", false);
	if ($hideinfo == FALSE || $correcthash == TRUE || User::getCurrent()->isAdmin() || User::getCurrent()->isCheckuser() )
    {
		$smarty->assign("showinfo", true);
    }
    
	if ($hideinfo == FALSE || $correcthash == TRUE || User::getCurrent()->isAdmin() || User::getCurrent()->isCheckuser() ) {
		$smarty->assign("proxyip", $request->getForwardedIp());
		if ($request->getForwardedIp()) {
			$smartyproxies = array(); // Initialize array to store data to be output in Smarty template.
			$smartyproxiesindex = 0;
			
			$proxies = explode(",", $request->getForwardedIp());
			$proxies[] = $request->getIp();
			
			$origin = $proxies[0];
			$smarty->assign("origin", $origin);
			
			$proxies = array_reverse($proxies);
			$trust = true;
            global $rfc1918ips;

            foreach($proxies as $proxynum => $p) {
                $p2 = trim($p);
				$smartyproxies[$smartyproxiesindex]['ip'] = $p2;

                // get data on this IP.
				$trusted = isXffTrusted($p2);
				$ipisprivate = ipInRange($rfc1918ips, $p2);
                
                if( !$ipisprivate) 
                {
				    $iprdns = $rdnsProvider->getRdns($p2);
                    $iplocation = $locationProvider->getIpLocation($p2);
                }
                else
                {
                    // this is going to fail, so why bother trying?
                    $iprdns = false;
                    $iplocation = false;
                }
                
                // current trust chain status BEFORE this link
				$pretrust = $trust;
				
                // is *this* link trusted?
				$smartyproxies[$smartyproxiesindex]['trustedlink'] = $trusted;
                
                // current trust chain status AFTER this link
                $trust = $trust & $trusted;
                if($pretrust && $p2 == $origin)
                {
                    $trust = true;   
                }
				$smartyproxies[$smartyproxiesindex]['trust'] = $trust;
				
				$smartyproxies[$smartyproxiesindex]['rdnsfailed'] = $iprdns === false;
				$smartyproxies[$smartyproxiesindex]['rdns'] = $iprdns;
				$smartyproxies[$smartyproxiesindex]['routable'] = ! $ipisprivate;
				
				$smartyproxies[$smartyproxiesindex]['location'] = $iplocation;
				
                if( $iprdns == $p2 && $ipisprivate == false) {
					$smartyproxies[$smartyproxiesindex]['rdns'] = NULL;
				}
                
				$smartyproxies[$smartyproxiesindex]['showlinks'] = (!$trust || $p2 == $origin) && !$ipisprivate;
                
				$smartyproxiesindex++;
			}
			
			$smarty->assign("proxies", $smartyproxies);
		}
	}

	global $protectReservedRequests, $defaultRequestStateKey;
	
	$smarty->assign("isprotected", isProtected($request->getId()));
    
	$smarty->assign("defaultstate", $defaultRequestStateKey);
	$smarty->assign("requeststates", $availableRequestStates);
		
	$spoofs = getSpoofs( $request->getName() );
	$smarty->assign("spoofs", $spoofs);
	
	// START LOG DISPLAY
	$loggerclass = new LogPage();
	$loggerclass->filterRequest = $request->getId();
	$logs = $loggerclass->getRequestLogs();
	
	$comments = $request->getComments();
    
    foreach ($comments as $c) {
		$logs[] = array(
            'time' => $c->getTime(), 
            'user' => $c->getUserObject()->getUsername(),
            'userid' => $c->getUser(),
            'description' => '', 
            'target' => 0, 
            'comment' => $c->getComment(), 
            'action' => "comment", 
            'security' => $c->getVisibility(), 
            'id' => $c->getId()
            );
	}
    
	if(trim($request->getComment()) !== "") {
		$logs[] = array(
			'time'=> $request->getDate(), 
			'user'=> $request->getName(), 
			'description' => '',
			'target' => 0, 
			'comment' => $request->getComment(), 
			'action' => "comment", 
			'security' => ''
			);
	}
	
	
	$namecache = array();
	
	if ($logs) {
		$logs = doSort($logs);
		foreach ($logs as &$row) {
			$row['canedit'] = false;
            
			if(!isset($row['security'])) {
				$row['security'] = '';
			}
            
            if(!isset($row['userid']))
            {
                if(!isset($namecache[$row['user']])) {
                    $row['userid'] = getUserIdFromName($row['user']);
                } else {
                    $row['userid'] = $namecache[($row['user'])];
                }
            }
            
			if($row['action'] == "comment") {
				$row['entry'] = htmlentities($row['comment'], ENT_QUOTES, 'UTF-8');
                
				global $enableCommentEditing;
				if($enableCommentEditing && (User::getCurrent()->isAdmin() || User::getCurrent()->isCheckuser() || User::getCurrent()->getId() == $row['userid']) && isset($row['id']))
                {
					$row['canedit'] = true;
                }
			} elseif($row['action'] == "Closed custom-n" ||$row['action'] == "Closed custom-y"  ) {
				$row['entry'] = "<em>" .$row['description'] . "</em><br />" . str_replace("\n", '<br />', htmlentities($row['comment'], ENT_QUOTES, 'UTF-8'));
			} else {
				foreach($availableRequestStates as $deferState)
                {
					$row['entry'] = "<em>" . str_replace("deferred to ".$deferState['defertolog'],"deferred to ".$deferState['deferto'],$row['description']) . "</em>"; //#35: The log text(defertolog) should not be displayed to the user, deferto is what should be displayed
                }
			}
		} // /foreach
		unset($row);
	} // if($logs)
    
	$smarty->assign("zoomlogs", $logs);


	// START OTHER REQUESTS BY IP AND EMAIL STUFF
	
	// Displays other requests from this ip.

    // assign to user
	$userListQuery = "SELECT username FROM user WHERE status = 'User' or status = 'Admin';";
	$userListResult = gGetDb()->query($userListQuery);
    $userListData = $userListResult->fetchAll(PDO::FETCH_COLUMN);
    $userListProcessedData = array();
    foreach ($userListData as $userListItem)
    {
        $userListProcessedData[] = "\"" . htmlentities($userListItem) . "\"";
    }
    
	$userList = '[' . implode(",", $userListProcessedData) . ']';	
    $smarty->assign("jsuserlist", $userList);
    // end: assign to user
    
    // TODO: refactor this!
	$createreasons = EmailTemplate::getActiveTemplates(/* forCreated */ 1);
	$smarty->assign("createreasons", $createreasons);
	
	$declinereasons = EmailTemplate::getActiveTemplates(/* forCreated */ 0);
	$smarty->assign("declinereasons", $declinereasons);
    
    $allcreatereasons = EmailTemplate::getAllActiveTemplates(/* forCreated */ 1);
	$smarty->assign("allcreatereasons", $allcreatereasons);
	
	$alldeclinereasons = EmailTemplate::getAllActiveTemplates(/* forCreated */ 0);
	$smarty->assign("alldeclinereasons", $alldeclinereasons);
	
	return $smarty->fetch("request-zoom.tpl");
}
