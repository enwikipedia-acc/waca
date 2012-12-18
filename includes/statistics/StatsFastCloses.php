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

class StatsFastCloses extends StatisticsPage
{
	function execute()
	{
		$query = <<<QUERY
SELECT
  Closed.log_pend AS Request,
  Closed.log_user AS User,
  TIMEDIFF(Closed.log_time, Reserved.log_time) AS "Time Taken",
  mail_desc AS "Close Type",
  Closed.log_time AS "Date"
FROM acc_log Closed
INNER JOIN acc_log Reserved 
  ON Closed.log_pend = Reserved.log_pend
INNER JOIN closes c
  ON c.`CONCAT("Closed ",mail_id)` = Closed.log_action
WHERE
  Closed.log_action != "Closed 4"
  AND
  Closed.log_action LIKE "Closed%"
  AND
  Reserved.log_action = "Reserved"
  AND
  TIMEDIFF(Closed.log_time, Reserved.log_time) < "00:00:30"
  AND
  Closed.log_user = Reserved.log_user
  AND
  TIMEDIFF(Closed.log_time, Reserved.log_time) > "00:00:00"
  AND
  DATE(Closed.log_time) > DATE(NOW()-INTERVAL 3 MONTH)
ORDER BY 
  TIMEDIFF(Closed.log_time, Reserved.log_time) ASC
;
QUERY;
		global $tsurl;
		$qb = new QueryBrowser();
		$qb->tableCallbackFunction = "statsFastClosesRowCallback";
		$r = $qb->executeQueryToTable($query); 
		echo mysql_error();

		return $r;
	}
	function getPageName()
	{
		return "FastCloses";
	}
	function getPageTitle()
	{
		return "Requests closed less than 30 seconds after reservation in the past 3 months";
	}
	function isProtected()
	{
		return true;
	}
	
	function requiresWikiDatabase()
	{
		return false;		
	}
}

function statsFastClosesRowCallback($row, $currentreq)
{
	$out.=  '<tr';
	if ($currentreq % 2 == 0) {
		$out.=  ' class="alternate">';
	} else {
		$out.=  '>';
	}
	
	$colid = 0;
	foreach ($row as $cell) {
		$out .= "<td>" ;
		
		if($colid == 0) $out .= "<a href=\"" . $tsurl . "/acc.php?action=zoom&id=" . $cell . "\">";
		
		$out .= $cell;
		
		if($colid == 0) $out .= "</a>";
		
		$out .= "</td>";
		$colid++;
	}

	$out.="</tr>";
	
	return $out;
}

