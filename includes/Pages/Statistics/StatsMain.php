<?php
namespace Waca\Pages\Statistics;

use Waca\PageBase;
use Waca\SecurityConfiguration;

class StatsMain extends PageBase
{
	public function main()
	{
		$this->assign('statsPageTitle', 'Account Creation Statistics');

		$statsPages = array(
			'fastCloses'       => 'Requests closed less than 30 seconds after reservation in the past 3 months',
			'idUsers'          => 'All identified users',
			'inactiveUsers'    => 'Inactive tool users',
			'monthlyStats'     => 'Monthly Statistics',
			'reservedRequests' => 'All currently reserved requests',
			'templateStats'    => 'Template Stats',
			'topCreators'      => 'Top Account Creators',
			'users'            => 'Account Creation Tool users',
		);

		$this->smallStats();

		$this->assign('statsPages', $statsPages);

		$graphList = array('day', '2day', '4day', 'week', '2week', 'month', '3month');
		$this->assign('graphList', $graphList);

		$this->setTemplate('statistics/main.tpl');
	}

	/**
	 * Gets the relevant statistics from the database for the small statistics table
	 */
	private function smallStats()
	{
		$database = $this->getDatabase();
		$requestsQuery = <<<'SQL'
SELECT COUNT(*) FROM request WHERE status = :status AND emailconfirm = 'Confirmed';
SQL;

		$requestsStatement = $database->prepare($requestsQuery);

		// TODO: use the request states thing here.

		// Open Requests
		$requestsStatement->execute(array(':status' => 'Open'));
		$open = $requestsStatement->fetchColumn();
		$requestsStatement->closeCursor();
		$this->assign('statsOpen', $open);

		// Admin Requests
		$requestsStatement->execute(array(':status' => 'Flagged users'));
		$admin = $requestsStatement->fetchColumn();
		$requestsStatement->closeCursor();
		$this->assign('statsAdmin', $admin);

		// Checkuser Requests
		$requestsStatement->execute(array(':status' => 'Checkuser'));
		$checkuser = $requestsStatement->fetchColumn();
		$requestsStatement->closeCursor();
		$this->assign('statsCheckuser', $checkuser);

		// Unconfirmed requests
		$unconfirmedStatement = $database->query(<<<SQL
SELECT COUNT(*) FROM request WHERE emailconfirm != 'Confirmed' AND emailconfirm != '';
SQL
		);
		$unconfirmed = $unconfirmedStatement->fetchColumn();
		$unconfirmedStatement->closeCursor();
		$this->assign('statsUnconfirmed', $unconfirmed);

		$userStatusStatement = $database->prepare('SELECT COUNT(*) FROM user WHERE status = :status;');

		// Admin users
		$userStatusStatement->execute(array(':status' => 'Admin'));
		$adminUsers = $userStatusStatement->fetchColumn();
		$userStatusStatement->closeCursor();
		$this->assign('statsAdminUsers', $adminUsers);

		// Users
		$userStatusStatement->execute(array(':status' => 'User'));
		$users = $userStatusStatement->fetchColumn();
		$userStatusStatement->closeCursor();
		$this->assign('statsUsers', $users);

		// Suspended users
		$userStatusStatement->execute(array(':status' => 'Suspended'));
		$suspendedUsers = $userStatusStatement->fetchColumn();
		$userStatusStatement->closeCursor();
		$this->assign('statsSuspendedUsers', $suspendedUsers);

		// New users
		$userStatusStatement->execute(array(':status' => 'New'));
		$newUsers = $userStatusStatement->fetchColumn();
		$userStatusStatement->closeCursor();
		$this->assign('statsNewUsers', $newUsers);

		// Most comments on a request
		$mostCommentsStatement = $database->query(<<<SQL
SELECT request FROM comment GROUP BY request ORDER BY COUNT(*) DESC LIMIT 1;
SQL
		);
		$mostComments = $mostCommentsStatement->fetchColumn();
		$mostCommentsStatement->closeCursor();
		$this->assign('mostComments', $mostComments);
	}

	public function getSecurityConfiguration()
	{
		return SecurityConfiguration::internalPage();
	}
}
