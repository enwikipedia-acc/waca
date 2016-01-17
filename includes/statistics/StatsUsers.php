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

class StatsUsers extends StatisticsPage
{
	protected function execute()
	{
		if (!isset($_GET['user'])) {
			return $this->getUserList();
		}
		else {
			return $this->getUserDetail($_GET['user']);
		}
	}

	public function getPageTitle()
	{
		return "Account Creation Tool users";
	}

	public function getPageName()
	{
		return "Users";
	}

	public function isProtected()
	{
		return false;
	}

	private function getUserList()
	{
		$lists = array(
			"Admin" => User::getAllWithStatus("Admin", gGetDb()),
			"User" => User::getAllWithStatus("User", gGetDb()),
			"CheckUsers" => User::getAllCheckusers(gGetDb())
		);

		global $smarty;
		$smarty->assign("lists", $lists);
		return $smarty->fetch("statistics/users.tpl");
	}

	private function getUserDetail($userId)
	{
		$database = gGetDb();

		$user = User::getById($userId, $database);
		if ($user == false) {
			return BootstrapSkin::displayAlertBox("User not found", "alert-error", "Error", true, false, true);
		}

		global $smarty;

		$activitySummary = $database->prepare(<<<SQL
SELECT COALESCE(closes.mail_desc, log.action) AS action, COUNT(*) AS count
FROM log
INNER JOIN user ON log.user = user.id
LEFT JOIN closes ON log.action = closes.closes
WHERE user.username = :username
GROUP BY action;
SQL
		);
		$activitySummary->execute(array(":username" => $user->getUsername()));
		$activitySummaryData = $activitySummary->fetchAll(PDO::FETCH_ASSOC);

		$smarty->assign("user", $user);
		$smarty->assign("activity", $activitySummaryData);

		$usersCreatedQuery = $database->prepare(<<<SQL
SELECT log.timestamp time, request.name name, request.id id
FROM log
INNER JOIN request ON (request.id = log.objectid and log.objecttype = 'Request')
INNER JOIN user ON log.user = user.id
LEFT JOIN emailtemplate ON concat('Closed ', emailtemplate.id) = log.action
WHERE user.username = :username
    AND log.action LIKE 'Closed %'
    AND (emailtemplate.oncreated = '1' OR log.action = 'Closed custom-y')
ORDER BY log.timestamp;
SQL
		);
		$usersCreatedQuery->execute(array(":username" => $user->getUsername()));
		$usersCreated = $usersCreatedQuery->fetchAll(PDO::FETCH_ASSOC);
		$smarty->assign("created", $usersCreated);

		$usersNotCreatedQuery = $database->prepare(<<<SQL
SELECT log.timestamp time, request.name name, request.id id
FROM log
JOIN request ON request.id = log.objectid and log.objecttype = 'Request'
JOIN user ON log.user = user.id
LEFT JOIN emailtemplate ON concat('Closed ', emailtemplate.id) = log.action
WHERE user.username = :username
    AND log.action LIKE 'Closed %'
    AND (emailtemplate.oncreated = '0' OR log.action = 'Closed custom-n' OR log.action = 'Closed 0')
ORDER BY log.timestamp;
SQL
		);
		$usersNotCreatedQuery->execute(array(":username" => $user->getUsername()));
		$usersNotCreated = $usersNotCreatedQuery->fetchAll(PDO::FETCH_ASSOC);
		$smarty->assign("notcreated", $usersNotCreated);

		$accountLogQuery = $database->prepare(<<<SQL
SELECT
	user.username as log_user,
    log.action as log_action,
    log.timestamp as log_time,
    log.comment as log_cmt
FROM log
INNER JOIN user ON user.id = log.user
WHERE log.objectid = :userid
AND log.objecttype = 'User'
AND log.action IN ('Approved','Suspended','Declined','Promoted','Demoted','Renamed','Prefchange');
SQL
		);
		$accountLogQuery->execute(array(":userid" => $user->getId()));
		$accountLog = $accountLogQuery->fetchAll(PDO::FETCH_ASSOC);
		$smarty->assign("accountlog", $accountLog);

		return $smarty->fetch("statistics/userdetail.tpl");
	}

	public function requiresWikiDatabase()
	{
		return false;
	}
}
