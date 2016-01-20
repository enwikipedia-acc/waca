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
	protected function execute()
	{
		$query = <<<SQL
SELECT
  log_closed.objectid AS Request,
  user.username AS User,
  log_closed.user AS UserID,
  TIMEDIFF(log_closed.timestamp, log_reserved.timestamp) AS 'Time Taken',
  closes.mail_desc AS 'Close Type',
  log_closed.timestamp AS 'Date'

FROM log log_closed
INNER JOIN log log_reserved ON log_closed.objectid = log_reserved.objectid 
	AND log_closed.objecttype = log_reserved.objecttype
INNER JOIN closes ON closes.`closes` = log_closed.action
LEFT JOIN user ON log_closed.user = user.id

WHERE log_closed.action LIKE 'Closed%'
  AND log_reserved.action = 'Reserved'
  AND TIMEDIFF(log_closed.timestamp, log_reserved.timestamp) < '00:00:30'
  AND log_closed.user = log_reserved.user
  AND TIMEDIFF(log_closed.timestamp, log_reserved.timestamp) > '00:00:00'
  AND DATE(log_closed.timestamp) > DATE(NOW()-INTERVAL 3 MONTH)

ORDER BY TIMEDIFF(log_closed.timestamp, log_reserved.timestamp) ASC
;
SQL;

		$qb = new QueryBrowser();
		$qb->tableCallbackFunction = "statsFastClosesRowCallback";
		$qb->overrideTableTitles =
			array("Request", "User", "Time Taken", "Close Type", "Date");
		$qb->rowFetchMode = PDO::FETCH_NUM;
		$r = $qb->executeQueryToTable($query);

		return $r;
	}

	public function getPageName()
	{
		return "FastCloses";
	}

	public function getPageTitle()
	{
		return "Requests closed less than 30 seconds after reservation in the past 3 months";
	}

	public function isProtected()
	{
		return true;
	}

	public function requiresWikiDatabase()
	{
		return false;
	}
}

function statsFastClosesRowCallback($row, $currentreq)
{
	$out = '<tr>';

	global $baseurl;

	$rowCount = count($row);

	for ($colid = 0; $colid < $rowCount; $colid++) {
		$cell = $row[$colid];

		$out .= "<td>";

		if ($colid == 0) {
			$out .= "<a href=\"" . $baseurl . "/acc.php?action=zoom&id=" . $cell . "\">";
		}
		if ($colid == 1) {
			$out .= "<a href=\"" . $baseurl . "/statistics.php/Users?user=" . $row[++$colid] . "\">";
		}

		$out .= $cell;

		if ($colid == 0 || $colid == 2) {
			$out .= "</a>"; // colid is now 2 if triggered from above due to postinc
		}

		$out .= "</td>";
	}

	$out .= "</tr>";

	return $out;
}
