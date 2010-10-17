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
	
	
	private function getLog($offset = 0, $limit = 100)
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
		$logQuery = 'SELECT * FROM acc_log l ';
		
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
		
		$logQuery.= "ORDER BY log_time DESC ";
		
		if($limit != '')
		{
			if (!preg_match('/^[0-9]*$/',$limit)) {
				die('Invaild limit value passed.');
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
				$logList .="<li>$rlu Created template <a href=\"$tsurl/acc.php?action=templatemgmt&amp;view=$rlp\">$rlp</a>, at $rlt.</li>\n";
			}
			if ($rla == "DeletedTemplate") {
				$logList .="<li>$rlu Deleted template $rlp, at $rlt.</li>\n";
			}
			if ($rla == "EditedTemplate") {
				$logList .="<li>$rlu Edited template <a href=\"$tsurl/acc.php?action=templatemgmt&amp;view=$rlp\">$rlp</a>, at $rlt.</li>\n";
			}
			if ($rla == "EditedMessage") {
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
			if($this->showPager == true){
				if($offset != 0)
				{
					$backOffset = ($offset < $limit) ? 0 : $offset - $limit;

					$urlParams = $this->swapUrlParams($limit, $backOffset);
					
					$out.= '<a href="?'.$urlParams.'">Previous '.$limit.'</a> - ';
				}

				if($logListCount == $limit)
				{
					$forwardOffset = $offset + $limit;
					$urlParams = $this->swapUrlParams($limit, $forwardOffset);
					$out.= '<a href="?'.$urlParams.'">Next '.$limit.'</a>';
				}
			}
			
			$out.= "<ul>$logList</ul>";	
			
			if($this->showPager == true){
							if($offset != 0)
				{
					$backOffset = ($offset < $limit) ? 0 : $offset - $limit;

					$urlParams = $this->swapUrlParams($limit, $backOffset);
					
					$out.= '<a href="?'.$urlParams.'">Previous '.$limit.'</a> - ';
				}

				if($logListCount == $limit)
				{
					$forwardOffset = $offset + $limit;
					$urlParams = $this->swapUrlParams($limit, $forwardOffset);
					$out.= '<a href="?'.$urlParams.'">Next '.$limit.'</a>';
				}
			}
		}
		
		return $out;
		
	}	
	
}
