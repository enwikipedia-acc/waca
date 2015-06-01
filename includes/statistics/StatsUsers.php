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
            SELECT COALESCE(c.mail_desc, l.log_action) AS action, COUNT(*) AS count 
            FROM acc_log l 
            LEFT JOIN closes c ON l.log_action = c.closes 
            WHERE l.log_user = :username 
            GROUP BY action;
SQL
		);
		$activitySummary->execute(array(":username" => $user->getUsername()));
		$activitySummaryData = $activitySummary->fetchAll(PDO::FETCH_ASSOC);

		$smarty->assign("user", $user);
		$smarty->assign("activity", $activitySummaryData);

		$usersCreatedQuery = $database->prepare(<<<SQL
            SELECT l.log_time time, r.name name, r.id id 
            FROM acc_log l
            JOIN request r ON r.id = l.log_pend 
            LEFT JOIN emailtemplate e ON concat('Closed ', e.id) = l.log_action 
            WHERE l.log_user = :username 
                AND l.log_action LIKE 'Closed %' 
                AND (e.oncreated = '1' OR l.log_action = 'Closed custom-y') 
            ORDER BY l.log_time;
SQL
		);
		$usersCreatedQuery->execute(array(":username" => $user->getUsername()));
		$usersCreated = $usersCreatedQuery->fetchAll(PDO::FETCH_ASSOC);
		$smarty->assign("created", $usersCreated);

		$usersNotCreatedQuery = $database->prepare(<<<SQL
            SELECT l.log_time time, r.name name, r.id id 
            FROM acc_log l
            JOIN request r ON r.id = l.log_pend 
            LEFT JOIN emailtemplate e ON concat('Closed ', e.id) = l.log_action 
            WHERE l.log_user = :username 
                AND l.log_action LIKE 'Closed %' 
                AND (e.oncreated = '0' OR l.log_action = 'Closed custom-n' OR l.log_action='Closed 0') 
            ORDER BY l.log_time;
SQL
		);
		$usersNotCreatedQuery->execute(array(":username" => $user->getUsername()));
		$usersNotCreated = $usersNotCreatedQuery->fetchAll(PDO::FETCH_ASSOC);
		$smarty->assign("notcreated", $usersNotCreated);

		$accountLogQuery = $database->prepare(<<<SQL
            SELECT * 
            FROM acc_log l 
            WHERE l.log_pend = :userid 
	            AND log_action IN ('Approved','Suspended','Declined','Promoted','Demoted','Renamed','Prefchange');     
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
