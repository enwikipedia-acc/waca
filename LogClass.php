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
	
	/**
	 * @param integer $logListCount
	 */
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
			$logQuery .= 'log_action LIKE "'.$this->filterAction.'"';
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
		global $tsSQLlink, $session, $baseurl;
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
			
			if (substr($rla,0,strlen("Deferred")) == "Deferred") {
				$logList .="<li>$rlu $rla, <a href=\"$baseurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed") {
				$logList .="<li>$rlu $rla, <a href=\"$baseurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if (substr($row['log_action'],0,7) == "Closed ") {
				if ($row['log_action'] == "Closed 0") {
					$logList .="<li>$rlu Dropped, <a href=\"$baseurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
				} 
				else if ($row['log_action'] == "Closed custom") {
					$logList .="<li>$rlu Closed (Custom), <a href=\"$baseurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
				}
		   		else if ($row['log_action'] == "Closed custom-y") {
					$logList .="<li>$rlu Closed (Custom, Created), <a href=\"$baseurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
				}
				else if ($row['log_action'] == "Closed custom-n") {
					$logList .="<li>$rlu Closed (Custom, Not Created), <a href=\"$baseurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
				}
				else {
					$eid = mysql_real_escape_string(substr($row['log_action'],7));
                    $template = EmailTemplate::getById($eid, gGetDb());
					$ename = htmlentities($template->getName(),ENT_QUOTES,'UTF-8');
					$logList .="<li>$rlu Closed ($ename), <a href=\"$baseurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
				}
			}
			if ($rla == 'Email Confirmed') {
				$logList .="<li>$rlu email-confirmed request $rlp ($rlt)</li>\n";
			}
			if ($rla == "CreatedTemplate") {
				$logList .="<li>$rlu created <a href=\"$baseurl/acc.php?action=templatemgmt&amp;view=$rlp\">template $rlp</a>, at $rlt.</li>\n";
			}
			if ($rla == "DeletedTemplate") {
				$logList .="<li>$rlu deleted template $rlp, at $rlt.</li>\n";
			}
			if ($rla == "EditedTemplate") {
				$logList .="<li>$rlu edited <a href=\"$baseurl/acc.php?action=templatemgmt&amp;view=$rlp\">template $rlp</a>, at $rlt.</li>\n";
			}
			if ($rla == "Edited") {
                $message = InterfaceMessage::getById($rlp, gGetDb());
				$logList .="<li>$rlu Edited message <a href=\"$baseurl/acc.php?action=messagemgmt&amp;view=$rlp\">$rlp (" . $message->getDescription() . ")</a>, at $rlt.</li>\n";
			}
			if ($rla == "Promoted" || $rla == "Demoted" || $rla == "Approved" || $rla == "Suspended" || $rla == "Declined") {
				$uid = $rlp;
                $user = User::getById($uid, gGetDb());
				
				if ($user === false)
                {
					die("User $uid not found.");
                }
                
				$moreinfo = "";
				if ($rla == "Declined" || $rla == "Suspended" || $rla == "Demoted") {
					$moreinfo = " because \"$rlc\"";
				}
				$logList .="<li>$rlu $rla, User $rlp (" . $user->getUsername() . ") at $rlt$moreinfo.</li>\n";
			}
			if ($rla == "Renamed") {
                $data = unserialize($rlc);
				$logList .="<li>$rlu renamed ${data['old']} to ${data['new']} at $rlt.</li>\n";
			}
			if ($rla == "Prefchange") {
                $user = User::getById($rlp, gGetDb());
				if ($user === false)
                {
					die("User $rlp not found.");
                }
                
				$logList .="<li>$rlu changed user preferences for $rlp (" . $user->getUsername() . ") at $rlt</li>\n";
			}
			if ($rla == "Banned") {
				$query2 = 'SELECT target, duration FROM `ban` WHERE `id` = \'' .$rlp. '\'; '; 
				$result2 = mysql_query($query2);
				if (!$result2)
					Die("Query failed: $query2 ERROR: " . mysql_error());
				$row2 = mysql_fetch_assoc($result2);
				if ($row2['duration'] == "-1") {
					$until = "indefinitely";
				}
				else {
					$durationtime = date("F j, Y, g:i a", $row2['duration']);
				    $until = "until $durationtime";
				}
				$logList .="<li>$rlu banned ". $row2['target'] ." $until at $rlt ($rlc)</li>";
			}
			if ($rla == "Unbanned") {
                $ban = Ban::getById($rlp, gGetDb());
                if ($ban) // Deal with bans from when unbanning resulted in the ban's row being deleted.
                	$bantarget = " (" . $ban->getTarget() . ") ";
                else
                	$bantarget = " ";
				$logList .="<li>$rlu unbanned ban ID $rlp". $bantarget ."at $rlt ($rlc)</li>";
			}
			if($rla == "Reserved") {
				$logList .= "<li>$rlu reserved request <a href=\"$baseurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt</li>";
			}
			if($rla == "SendReserved") {
				$logList .= "<li>$rlu sent reserved request <a href=\"$baseurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt</li>";
			}			
            if($rla == "ReceiveReserved") {
				$logList .= "<li>$rlu received a reserved request <a href=\"$baseurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt</li>";
			}
			if($rla == "Unreserved") {
				$logList .= "<li>$rlu unreserved request <a href=\"$baseurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt</li>";
			}
			if($rla == "BreakReserve") {
				$logList .= "<li>$rlu broke the reservation on <a href=\"$baseurl/acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a>, at $rlt</li>";
			}			
			if($rla == "EditComment-c") {
				$query4 = "SELECT request FROM comment WHERE id = $rlp;";
				$result4 = mysql_query($query4);
				if (!$result4)
					Die("Query failed: $query4 ERROR: " . mysql_error());
				$row4 = mysql_fetch_assoc($result4);
				$logList .= "<li>$rlu edited <a href=\"$baseurl/acc.php?action=zoom&amp;id=" . $row4['request'] ."\">comment $rlp</a>, at $rlt</li>";
			}
			if ($rla == "CreatedEmail") {
                $template = EmailTemplate::getById($rlp, gGetDb());
				$logList .="<li>$rlu created email <a href=\"$baseurl/acc.php?action=emailmgmt&amp;edit=$rlp\">$rlp (" . $template->getName() . ")</a>, at $rlt.</li>\n";
			}
			if ($rla == "EditedEmail") {
                $template = EmailTemplate::getById($rlp, gGetDb());
				$logList .="<li>$rlu edited email <a href=\"$baseurl/acc.php?action=emailmgmt&amp;edit=$rlp\">$rlp (" . $template->getName() . ")</a>, at $rlt.</li>\n";
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
		global $tsSQLlink, $session, $baseurl;
		
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
			if (substr($rla,0,strlen("Deferred")) == "Deferred") {

				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>strtolower($rla), 'target' => $rlp, 'comment' => $rlc, 'action' => "Deferred");
			}
			if ($row['log_action'] == "Closed") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>$rla, 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if (substr($row['log_action'],0,7) == "Closed ") {
				if ($row['log_action'] == "Closed 0") {
					$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"dropped", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
				}
				else if ($row['log_action'] == "Closed custom") {
					$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (custom reason)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
				}
				else if ($row['log_action'] == "Closed custom-y") {
					$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (custom reason - account created)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
				}
				else if ($row['log_action'] == "Closed custom-n") {
					$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed (custom reason - account not created)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
				}
				else {
                    $template = EmailTemplate::getById(substr($row['log_action'],7), gGetDb());
					$ename = htmlentities($template->getName(),ENT_QUOTES,'UTF-8');
					$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"closed ($ename)", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
				}
			}
			if ($rla == 'Email Confirmed') {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"email-confirmed", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "CreatedEmail") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"created email", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "CreatedTemplate") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"created template", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "DeletedTemplate") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"deleted template", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "EditedEmail") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"edited email", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "EditedTemplate") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"edited template", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "Edited") {
                $message = InterfaceMessage::getById($rlp, gGetDb());
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"edited message ". $message->getDescription(), 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "Promoted" || $rla == "Demoted" || $rla == "Approved" || $rla == "Suspended" || $rla == "Declined") {
                $user = User::getById($rlp, gGetDb());
                if($user === false)
                {
                    die("User $rlp not found");
                }
                    
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>strtolower($rla) . $user->getUsername(), 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "Renamed") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"renamed", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "Prefchange") {
                $user = User::getById($rlp, gGetDb());
                if($user === false)
                {
                    die("User $rlp not found");
                }

				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"changed user preferences for " . $user->getUsername(), 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if ($rla == "Banned") {
				$query2 = 'SELECT ban_target, ban_duration FROM `ban` WHERE `ban_target` = \'' .$rlp. '\'; '; 
				$result2 = mysql_query($query2, $tsSQLlink);
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
                $ban = Ban::getById($rlp, gGetDb());
                if ($ban) // Deal with bans from when unbanning resulted in the ban's row being deleted.
                	$bantarget = " (" . $ban->getTarget() . ")";
                else
                	$bantarget = "";
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"unbanned" . $bantarget, 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if($rla == "Reserved") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"reserved", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}			
            if($rla == "SendReserved") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"sent reservation", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
            if($rla == "ReceiveReserved") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"received reservation", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if($rla == "Unreserved") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"unreserved", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if($rla == "BreakReserve") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"broke the reservation", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
			}
			if($rla == "EditComment-r") {
				$out[] = array('time'=> $rlt, 'user'=>$rlu, 'description' =>"edited a comment", 'target' => $rlp, 'comment' => $rlc, 'action' => $rla, 'security' => 'user');
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
				case "Approved":
				case "Banned":
				case "CreatedEmail":
				case "CreatedTemplate":
				case "Declined":
				case "DeletedTemplate":
				case "Edited":
				case "EditedEmail":
				case "EditedTemplate":
				case "Prefchange":
				case "Promoted":
				case "Renamed":
				case "Suspended":
				case "Unbanned":
					break;
				case "Deferred":
				case "Closed":
				case "Closed 0":
				case "Closed custom":
				case "Closed custom-n":
				case "Closed custom-y":
				case "Email Confirmed":
				case "Reserved":
				case "SendReserved":
				case "ReceiveReserved":
				case "Unreserved":
				case "BreakReserve":
				case "EditComment-r":
					$requestlog[] = $entry;
					break;
				default:
				$requestlog[] = $entry;
					break;
			}
		}
		
		return $requestlog;
	}
}
