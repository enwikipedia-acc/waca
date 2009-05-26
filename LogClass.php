<?php
class LogPage
{
	var $filterUser = "";
	var $filterAction = "";
	var $filterRequest = "";
	
	
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
		$logQuery = 'SELECT * FROM acc_log l';
		
		if($filterRequest != '')
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
			$logQuery = 'log_pend LIKE "'.$filterRequest.'"';
		}
		if($filterUser != '')
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
			$logQuery = 'log_user LIKE "'.$filterUser.'"';
		}
		if($filterAction != '')
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
			$logQuery = 'log_action LIKE "'.$filterAction.'"';
		}
		
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
	
	public function showListLog($offset, $limit)
	{
		$result = $this->getLog($offset, $limit);
		$logList = "";
		while ($row = mysql_fetch_assoc($result)) {
			$rlu = $row['log_user'];
			$rla = $row['log_action'];
			$rlp = $row['log_pend'];
			$rlt = $row['log_time'];
			$rlc = $row['log_cmt'];
			if ($row['log_time'] == "0000-00-00 00:00:00") {
				$row['log_time'] = "Date Unknown";
			}
			if ($row['log_action'] == "Deferred to admins" || $rla == "Deferred to users") {
	
				$logList .="<li>$rlu $rla, <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed") {
				$logList .="<li>$rlu $rla, <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 0") {
				$logList .="<li>$rlu Dropped, <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 1") {
				$logList .="<li>$rlu Closed (Account created), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 2") {
				$logList .="<li>$rlu Closed (Too Similar), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 3") {
				$logList .="<li>$rlu Closed (Taken), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 4") {
				$logList .="<li>$rlu Closed (Username vio), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 5") {
				$logList .="<li>$rlu Closed (Technical Impossibility), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Closed 6") {
				$logList .="<li>$rlu Closed (Custom reason), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
			}
			if ($row['log_action'] == "Blacklist Hit" || $row['log_action'] == "DNSBL Hit") {
				$logList .="<li>$rlu <strong>Rejected by Blacklist</strong> $rlp, $rlc at $rlt.</li>\n";
			}
			if ($rla == "Edited") {
				$mid = $rlp;
				$query3 = "SELECT * FROM acc_emails WHERE mail_id = '$mid';";
				$result3 = mysql_query($query3, $tsSQLlink);
				if (!$result3)
					Die("Query failed: $query ERROR: " . mysql_error());
				$row3 = mysql_fetch_assoc($result3);
				$logList .="<li>$rlu Edited Message <a href=\"acc.php?action=messagemgmt&amp;view=$rlp\">$rlp (" . $row3['mail_desc'] . ")</a>, at $rlt.</li>\n";
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
					Die("Query failed: $query ERROR: " . mysql_error());
				$row2 = mysql_fetch_assoc($result2);
				$logList .="<li>$rlu changed user preferences for $rlp (" . $row2['user_name'] . ") at $rlt</li>\n";
			}
			if ($rla == "Unbanned") {
				// gonna need to think of another way to do this:
				//    * can't look in the ban table, the ban is not there any more
				//    * possibly look for the DNSBL or Banned entry in the log table, then parse to get type.
			
				//$query2 = 'SELECT * FROM `acc_ban` WHERE `ban_id` = '.$rlp.'; '; 
				//$result2 = mysql_query($query2);
				//if (!$result2)
				//	Die("Query failed: $query2 ERROR: " . mysql_error());
				//$row2 = mysql_fetch_assoc($result2);
				//$logList .="<li>$rlu unbanned ban ID $rlp of type ".$row2['ban_type']." targeted at ".$row2['ban_target']." at $rlt</li>\n";
				
				$logList .="<li>$rlu unbanned ban ID $rlp at $rlt</li>";
			}
		}
		
		if( $logList = "")
		{
			echo "<i>No results</i>";	
		}
		else
		{
			// TODO: pager functions
			
			echo "<ul>$logList</ul>";	
		}
		
	}	
	
}