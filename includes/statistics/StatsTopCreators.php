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

class StatsTopCreators extends StatisticsPage
{
	protected function execute()
	{
		global $smarty;

		$qb = new QueryBrowser();
		$qb->numberedList = true;
		$qb->numberedListTitle = "Position";

		$qb->tableCallbackFunction = "statsTopCreatorsRowCallback";
		$qb->overrideTableTitles = array("# Created", "Username");

		// Retrieve all-time stats
		$top5aout = $qb->executeQueryToTable(<<<SQL
SELECT
	/* StatsTopCreators::execute()/top5aout */
    COUNT(*),
    log.user user_id,
    user.username log_user,
    user.status user_level
FROM log
LEFT JOIN emailtemplate ON concat('Closed ', emailtemplate.id) = log.action
INNER JOIN user ON user.id = log.user
WHERE emailtemplate.oncreated = '1'
   OR log.action = 'Closed custom-y'

GROUP BY log.user, user.username, user.status
ORDER BY COUNT(*) DESC;
SQL
		);

		// Retrieve all-time stats for active users only
		$top5activeout = $qb->executeQueryToTable(<<<SQL
SELECT
	/* StatsTopCreators::execute()/top5activeout */
    COUNT(*),
    log.user user_id,
    user.username log_user,
    user.status user_level
FROM log
LEFT JOIN emailtemplate ON concat('Closed ', emailtemplate.id) = log.action
INNER JOIN user ON user.id = log.user
WHERE
	(emailtemplate.oncreated = 1 OR log.action = 'Closed custom-y')
    AND user.status != 'Suspended'
GROUP BY user.username, user.id
ORDER BY COUNT(*) DESC;
SQL
		);

		// Retrieve today's stats (so far)
		$now = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")));
		$top5out = $qb->executeQueryToTable(<<<SQL
SELECT
	/* StatsTopCreators::execute()/top5out */
    COUNT(*),
    log.user user_id,
    user.username log_user,
    user.status user_level
FROM log
INNER JOIN user ON user.id = log.user
LEFT JOIN emailtemplate ON CONCAT('Closed ', emailtemplate.id) = log.action
WHERE (emailtemplate.oncreated = '1' OR log.action = 'Closed custom-y')
  AND log.timestamp LIKE '{$now}%'
GROUP BY log.user, user.username
ORDER BY COUNT(*) DESC;
SQL
		);

		// Retrieve Yesterday's stats
		$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1));
		$top5yout = $qb->executeQueryToTable(<<<SQL
SELECT
	/* StatsTopCreators::execute()/top5yout */
    COUNT(*),
    log.user user_id,
    user.username log_user,
    user.status user_level
FROM log
INNER JOIN user ON user.id = log.user
LEFT JOIN emailtemplate ON CONCAT('Closed ', emailtemplate.id) = log.action
WHERE (emailtemplate.oncreated = '1' OR log.action = 'Closed custom-y')
  AND log.timestamp LIKE '{$yesterday}%'
GROUP BY log.user, user.username
ORDER BY COUNT(*) DESC;
SQL
		);

		// Retrieve last 7 days
		$lastweek = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 7));
		$top5wout = $qb->executeQueryToTable(<<<SQL
SELECT
	/* StatsTopCreators::execute()/top5wout */
    COUNT(*),
    log.user user_id,
    user.username log_user,
    user.status user_level
FROM log
INNER JOIN user ON user.id = log.user
LEFT JOIN emailtemplate ON CONCAT('Closed ', emailtemplate.id) = log.action
WHERE (emailtemplate.oncreated = '1' OR log.action = 'Closed custom-y')
  AND log.timestamp > '{$lastweek}%'
GROUP BY log.user, user.username
ORDER BY COUNT(*) DESC;
SQL
		);

		// Retrieve last month's stats
		$lastmonth = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 28));
		$top5mout = $qb->executeQueryToTable(<<<SQL
SELECT
	/* StatsTopCreators::execute()/top5mout */
    COUNT(*),
    log.user user_id,
    user.username log_user,
    user.status user_level
FROM log
INNER JOIN user ON user.id = log.user
LEFT JOIN emailtemplate ON CONCAT('Closed ', emailtemplate.id) = log.action
WHERE (emailtemplate.oncreated = '1' OR log.action = 'Closed custom-y')
  AND log.timestamp > '{$lastmonth}%'
GROUP BY log.user, user.username
ORDER BY COUNT(*) DESC;
SQL
		);

		// Put it all together
		$smarty->assign("top5aout", $top5aout);
		$smarty->assign("top5activeout", $top5activeout);
		$smarty->assign("top5out", $top5out);
		$smarty->assign("top5yout", $top5yout);
		$smarty->assign("top5wout", $top5wout);
		$smarty->assign("top5mout", $top5mout);

		return $smarty->fetch("statistics/topcreators.tpl");
	}

	public function getPageTitle()
	{
		return "Top Account Creators";
	}

	public function getPageName()
	{
		return "TopCreators";
	}

	public function isProtected()
	{
		return false;
	}

	public function requiresWikiDatabase()
	{
		return false;
	}
}

function statsTopCreatorsRowCallback($row, $rowno)
{
	$out = "<tr";
	if ($row['log_user'] == User::getCurrent()->getUsername()) {
		$out .= ' class="info"';
	}

	$out .= '>';

	$out .= '<td>' . $rowno . '</td>';
	$out .= '<td>' . $row['COUNT(*)'] . '</td>';

	global $baseurl;
	$out .= '<td><a ';

	if ($row['user_level'] == "Suspended") {
		$out .= 'class="muted" ';
	}
	if ($row['user_level'] == "Admin") {
		$out .= 'class="text-success" ';
	}

	$out .= 'href="' . $baseurl . '/statistics.php?page=Users&amp;user=' . $row['user_id'] . '">' . $row['log_user'] . '</a></td>';

	$out .= '</tr>';

	return $out;
}
