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

class LogPage
{
	var $filterUser = "";
	var $filterAction = "";
	var $filterRequest = "";
	var $showPager = true;
	
	private function createPager($offset, $limit, $logListCount, $count) {
		$pager = '';
		if($offset != 0)
		{
			$backOffset = ($offset < $limit) ? 0 : $offset - $limit;
			$latestLink = $this->swapUrlParams($limit, 0);
			$urlParams = $this->swapUrlParams($limit, $backOffset);
			$pager.= '<a href="?'.$latestLink.'">Latest</a> | <a href="?'.$urlParams.'">Previous '.$limit.'</a> | ';
		}

		if(($offset + $limit) < $count)
		{
			$forwardOffset = $offset + $limit;
			$earliestLink = $this->swapUrlParams($limit, $count - $limit);
			$urlParams = $this->swapUrlParams($limit, $forwardOffset);
			$pager.= '<a href="?'.$urlParams.'">Next '.$limit.'</a> | <a href="?'.$earliestLink.'">Earliest</a>';
		}
		elseif ($offset != 0) {
			$pager = substr($pager, 0, -3);
		}

		$pager .= "<br /> Set limit: ";
		$potentialLimits = array(20, 50, 100, 250, 500);
		foreach ($potentialLimits as $potentialLimit) {
			if ($potentialLimit != $limit && $potentialLimit < ($count - $offset)) {
				$urlParams = $this->swapUrlParams($potentialLimit, $offset);
				$pager .= "<a href='?$urlParams'>$potentialLimit</a>";
			} else {
				$pager .= $potentialLimit;
			}
			$pager .= " | ";
		}
		$pager = substr($pager, 0, -3);
		return $pager;
	}
	
	// accepts "infinity" for limit parameter
	private function getLog($offset = 0, $limit = 100, $count = false)
	{
		//CREATE TABLE `acc_log` (
		//`log_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		//`log_pend` VARCHAR( 255 ) NOT NULL ,
		//`log_user` VARCHAR( 255 ) NOT NULL ,
		//`log_action` VARCHAR( 255 ) NOT NULL ,
		//`log_time` DATETIME NOT NULL ,
		//`log_cmt` BLOB NOT NULL
		//) ENGINE = InnoDB; 
		

		global $tsSQLlink;
		
		$sqlWhereAdded = 0;
		if (!$count)
			$logQuery = 'SELECT * FROM acc_log l ';
		else
			$logQuery = 'SELECT COUNT(*) AS `count` FROM acc_log l ';
		
		if($this->filterRequest != '')
		{
			if($sqlWhereAdded == 0)
			{
				$logQuery.= "WHERE ";
				$sqlWhereAdded = 1;
			}
			else
			{
				$logQuery.= "AND ";
			}
			$logQuery .= 'log_pend LIKE "'.$this->filterRequest.'"';
		}
		if($this->filterUser != '')
		{
			if($sqlWhereAdded == 0)
			{
				$logQuery.= "WHERE ";
				$sqlWhereAdded = 1;
			}
			else
			{
				$logQuery.= "AND ";
			}
			$logQuery .= 'log_user LIKE "'.$this->filterUser.'"';
		}
		if($this->filterAction != '')
		{
			if($sqlWhereAdded == 0)
			{
				$logQuery.= "WHERE ";
				$sqlWhereAdded = 1;
			}
			else
			{
				$logQuery.= "AND ";
			}
			$logQuery .= 'log_action = "'.$this->filterAction.'"';
		}
		
		if (!$count) {
			$logQuery.= "ORDER BY log_time DESC ";
			
		
			if($limit != "infinity")
			{
				if($limit != '')
				{
					if (!preg_match('/^[0-9]*$/',$limit)) {
						die('Invalid limit value passed.');
					}
					$limit = sanitize($limit);
				}
				else
				{
					$limit = 100;
				}
				
				$logQuery.=' LIMIT '.$limit;
				
				if($offset != '')
				{
					if (!preg_match('/^[0-9]*$/',$offset)) {
						die('Invaild limit value passed.');
					}
					$offset = sanitize($offset);

					$logQuery.=' OFFSET '.$offset;
				}
			}
		}
		
		$logResult = mysql_query($logQuery, $tsSQLlink) or sqlerror(mysql_error() . "<i>$logQuery</i>","Ooops in LogPage class");
		
		return $logResult;
	}
	
	private function swapUrlParams($limit, $offset)
	{
		global $enableSQLError;
		$urlParams = '';
		$doneFrom = false;
		$doneLimit = false;
		if($enableSQLError == 1){ echo "<!--" ; echo print_r($_GET); echo "-->";}
		foreach($_GET as $key => $value)
		{
			switch($key)
			{
				case "from":
					$value = $offset;
					$doneFrom = true;
					break;
				case "limit":
					$value = $limit;
					$doneLimit=true;
					break;
			}
			
			$urlParams.= '&amp;' . $key . '=' . $value;
		}
		if(!$doneFrom)
		{
			$urlParams.= '&amp;from=' . $offset;
		}
		if(!$doneLimit)
		{
			$urlParams.= '&amp;limit=' . $limit;
		}
		return substr_replace($urlParams, '&amp;', 0, 5);		
	}
	
	public function showListLog($offset, $limit)
	{
		global $tsSQLlink, $session, $tsurl;
		$out="";
		
		$result = $this->getLog($offset, $limit);
		$count = current(mysql_fetch_array($this->getLog($offset, null, true)));
		$logList = "";
		$logListCount = 0;
		while ($row = mysql_fetch_assoc($result)) {
			$rlu = $row['log_user'];
			$rla = $row['log_action'];
			$rlp = $row['log_pend'];
			$rlt = $row['log_time'];
			$rlc = $row['log_cmt'];
			
			if ($row['log_time'] == "0000-00-00 00:00:00") {
				$row['log_time'] = "Date Unknown";
			}
			if ($row['log_action'] == "Deferred to admins" || $rla == "Deferred to users" || $rla == "Deferred to checkusers") {
	
				$logList .="<li>$rlu $rla, <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed") {
				$logList .="<li>$rlu $rla, <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 0") {
				$logList .="<li>$rlu Dropped, <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 1") {
				$logList .="<li>$rlu Closed (Account created), <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 2") {
				$logList .="<li>$rlu Closed (Too Similar), <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 3") {
				$logList .="<li>$rlu Closed (Taken), <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 4") {
				$logList .="<li>$rlu Closed (Username vio), <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 5") {
				$logList .="<li>$rlu Closed (Technical Impossibility), <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 26") {
				$logList .="<li>$rlu Closed (Taken in SUL), <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 30") {
				$logList .="<li>$rlu Closed (Password Reset), <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed custom") {
				$logList .="<li>$rlu Closed (Custom), <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
		    if ($row['log_action'] == "Closed custom-y") {
				$logList .="<li>$rlu Closed (Custom, Created), <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed custom-n") {
				$logList .="<li>$rlu Closed (Custom, Not Created), <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Blacklist Hit" || $row['log_action'] == "DNSBL Hit") {
				$logList .="<li>$rlu <strong>Rejected by Blacklist</strong> $rlp, $rlc at $rlt.</li>\n";
			}
			if ($rla == 'Email Confirmed') {
				$logList .="<li>$rlu email-confirmed request $rlp ($rlt)</li>\n";
			}
			if ($rla == "CreatedTemplate") {
				$logList .="<li>$rlu created <a href=\"$tsurl/acc.php?action=templatemgmt&amp;view=$rlp\">template $rlp</a>, at $rlt.</li>\n";
			}
			if ($rla == "DeletedTemplate") {
				$logList .="<li>$rlu deleted template $rlp, at $rlt.</li>\n";
			}
			if ($rla == "EditedTemplate") {
				$logList .="<li>$rlu edited <a href=\"$tsurl/acc.php?action=templatemgmt&amp;view=$rlp\">template $rlp</a>, at $rlt.</li>\n";
			}
			if ($rla == "Edited") {
				$mid = $rlp;
				$query3 = "SELECT * FROM acc_emails WHERE mail_id = '$mid';";
				$result3 = mysql_query($query3, $tsSQLlink);
				if (!$result3)
					Die("Query failed: $query ERROR: " . mysql_error());
				$row3 = mysql_fetch_assoc($result3);
				$logList .="<li>$rlu Edited message <a href=\"$tsurl/acc.php?action=messagemgmt&amp;view=$rlp\">$rlp (" . $row3['mail_desc'] . ")</a>, at $rlt.</li>\n";
			}
			if ($rla == "Promoted" || $rla == "Demoted" || $rla == "Approved" || $rla == "Suspended" || $rla == "Declined") {
				$uid = $rlp;
				$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
				$result2 = mysql_query($query2, $tsSQLlink);
				if (!$result2)
					Die("Query failed: $query ERROR: " . mysql_error());
				$row2 = mysql_fetch_assoc($result2);
				$moreinfo = "";
				if ($rla == "Declined" || $rla == "Suspended" || $rla == "Demoted") {
					$moreinfo = " because \"$rlc\"";
				}
				$logList .="<li>$rlu $rla, User $rlp (" . $row2['user_name'] . ") at $rlt$moreinfo.</li>\n";
			}
			if ($rla == "Renamed") {
				$logList .="<li>$rlu renamed $rlc at $rlt.</li>\n";
			}
			if ($rla == "Prefchange") {
				$query2 = "SELECT user_name FROM acc_user WHERE user_id = '$rlp';";
				$result2 = mysql_query($query2, $tsSQLlink);
				if (!$result2)
					Die("Query failed: $query2 ERROR: " . mysql_error());
				$row2 = mysql_fetch_assoc($result2);
				$logList .="<li>$rlu changed user preferences for $rlp (" . $row2['user_name'] . ") at $rlt</li>\n";
			}
			if ($rla == "Banned") {
				$query2 = 'SELECT ban_target, ban_duration FROM `acc_ban` WHERE `ban_target` = \'' .$rlp. '\'; '; 
				$result2 = mysql_query($query2);
				if (!$result2)
					Die("Query failed: $query2 ERROR: " . mysql_error());
				$row2 = mysql_fetch_assoc($result2);
				if ($row2['ban_duration'] == "-1") {
					$until = "indefinitely";
				}
				else {
					$durationtime = date("F j, Y, g:i a", $row2['ban_duration']);
				    $until = "until $durationtime";
				}
				$logList .="<li>$rlu banned ". $row2['ban_target'] ." $until at $rlt ($rlc)</li>";
			}
			if ($rla == "Unbanned") {
				$query2 = 'SELECT ban_target FROM `acc_ban` WHERE `ban_id` = '.$rlp.'; '; 
				$result2 = mysql_query($query2);
				if (!$result2)
					Die("Query failed: $query2 ERROR: " . mysql_error());
				$row2 = mysql_fetch_assoc($result2);
				$logList .="<li>$rlu unbanned ban ID $rlp (". $row2['ban_target'] .") at $rlt ($rlc)</li>";
			}
			if($rla == "Reserved") {
				$logList .= "<li>$rlu reserved request <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt</li>";
			}
			if($rla == "Unreserved") {
				$logList .= "<li>$rlu unreserved request <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt</li>";
			}
			if($rla == "BreakReserve") {
				$logList .= "<li>$rlu broke the reservation on <a href=\"$tsurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a>, at $rlt</li>";
			}
			$logListCount++;
		}
		
		if( $logList == "")
		{
			$out.= "<i>No results</i>";	
		}
		else
		{
			if($this->showPager == true)
				$pager = $this->createPager($offset, $limit, $logListCount, $count);
			else
				$pager = '';
			$out.= "$pager<ul>$logList</ul>$pager";	
		}
		
		return $out;
		
	}	
	
	public function getArrayLog($offset=0, $limit="infinity")
	{
		global $tsSQLlink, $session, $tsurl;
		
		$out=array();
		$result = $this->getLog($offset, $limit);
		
		while ($row = mysql_fetch_assoc($result)) {
			$rlu = $row['log_user'];
			$rla = $row['log_action'];
			$rlp = $row['log_pend'];
			$rlt = $row['log_time'];
			$rlc = $row['log_cmt'];
			
			if ($rlt == "0000-00-00 00:00:00") {
				$rlt = "Date Unknown";
			}
			if (substr($rla,0,strlen("Deferred") == "Deferred")) {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>strtolower($rla), 'target' => $rlp, 'comment' => $rlc, 'action' => "Deferred");
			}
			if ($row['log_action'] == "Closed") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>$rla, 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($row['log_action'] == "Closed 0") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"dropped", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($row['log_action'] == "Closed 1") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (created)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($row['log_action'] == "Closed 2") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (too similar)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($row['log_action'] == "Closed 3") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (taken)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($row['log_action'] == "Closed 4") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (policy)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($row['log_action'] == "Closed 5") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (technical)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($row['log_action'] == "Closed 26") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (SUL)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($row['log_action'] == "Closed 30") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (password reset)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($row['log_action'] == "Closed custom") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (custom reason)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
		    if ($row['log_action'] == "Closed custom-y") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (custom reason - account created)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($row['log_action'] == "Closed custom-n") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (custom reason - account not created", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($row['log_action'] == "Blacklist Hit" || $row['log_action'] == "DNSBL Hit") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"rejected by blacklist", 'target' => $rlp, 'comment' => $rlc, 'action' => "Blacklist");
			}
			if ($rla == 'Email Confirmed') {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"email-confirmed", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "CreatedTemplate") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"created template", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "DeletedTemplate") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"deletd template", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "EditedTemplate") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"edited template", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "Edited") {
				$mid = $rlp;
				$query3 = "SELECT * FROM acc_emails WHERE mail_id = '$mid';";
				$result3 = mysql_query($query3, $tsSQLlink);
				if (!$result3)
					Die("Query failed: $query ERROR: " . mysql_error());
				$row3 = mysql_fetch_assoc($result3);
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"edited message ". $row3['mail_desc'], 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "Promoted" || $rla == "Demoted" || $rla == "Approved" || $rla == "Suspended" || $rla == "Declined") {
				$uid = mysql_real_escape_string($rlp, $tsSQLlink);
				$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
				$result2 = mysql_query($query2, $tsSQLlink);
				if (!$result2)
					Die("Query failed: $query ERROR: " . mysql_error());
				$row2 = mysql_fetch_assoc($result2);
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>strtolower($rla) . $row2['user_name'], 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "Renamed") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"renamed", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "Prefchange") {
				$query2 = "SELECT user_name FROM acc_user WHERE user_id = '$rlp';";
				$result2 = mysql_query($query2, $tsSQLlink);
				if (!$result2)
					Die("Query failed: $query2 ERROR: " . mysql_error());
				$row2 = mysql_fetch_assoc($result2);
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"changed user preferences for " . $row2['user_name'], 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "Banned") {
				$query2 = 'SELECT ban_target, ban_duration FROM `acc_ban` WHERE `ban_target` = \'' .$rlp. '\'; '; 
				$result2 = mysql_query($query2);
				if (!$result2)
					Die("Query failed: $query2 ERROR: " . mysql_error());
				$row2 = mysql_fetch_assoc($result2);
				if ($row2['ban_duration'] == "-1") {
					$until = "indefinitely";
				}
				else {
					$durationtime = date("F j, Y, g:i a", $row2['ban_duration']);
				    $until = "until $durationtime";
				}
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"banned ". $row2['ban_target'] ." $until", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "Unbanned") {
				$query2 = 'SELECT ban_target FROM `acc_ban` WHERE `ban_id` = '.$rlp.'; '; 
				$result2 = mysql_query($query2);
				if (!$result2)
					Die("Query failed: $query2 ERROR: " . mysql_error());
				$row2 = mysql_fetch_assoc($result2);
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"unbanned (". $row2['ban_target'] .")", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if($rla == "Reserved") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"reserved", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if($rla == "Unreserved") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"unreserved", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if($rla == "BreakReserve") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"broke the reservation", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
		}
		
		return $out;
	}
	
	public function getRequestLogs()
	{
		$entirelog = $this->getArrayLog();
		
		$requestlog = array();
		foreach($entirelog as $entry)
		{
			switch($entry['action']){
				case "Deferred":
				case "Closed":
				case "Closed 0":
				case "Closed 1":
				case "Closed 2":
				case "Closed 3":
				case "Closed 4":
				case "Closed 5":
				case "Closed 26":
				case "Closed 30":
				case "Closed custom":
				case "Closed customn-n":
				case "Closed custom-y":
				case "Email Confirmed":
				case "Reserved":
				case "Unreserved":
				case "BreakReserve":
					$requestlog[] = $entry;
					break;
				default:
					break;
			}
		}
		
		return $requestlog;
	}
}
