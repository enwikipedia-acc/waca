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
		$query = <<<QUERY
SELECT
  Closed.log_pend AS Request,
  Closed.log_user AS User,
  u.id AS UserID,
  TIMEDIFF(Closed.log_time, Reserved.log_time) AS "Time Taken",
  mail_desc AS "Close Type",
  Closed.log_time AS "Date"
FROM acc_log Closed
INNER JOIN acc_log Reserved
  ON Closed.log_pend = Reserved.log_pend
INNER JOIN closes c
  ON c.`closes` = Closed.log_action
LEFT JOIN user u
  ON Closed.log_user = u.username
WHERE
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
		global $baseurl;
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
