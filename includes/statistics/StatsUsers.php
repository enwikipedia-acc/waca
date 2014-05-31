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
	function execute()
	{
		if(!isset($_GET['user']))
		{
			return $this->getUserList();
		}
		else
		{
			return $this->getUserDetail($_GET['user']);
		}
	}
	
	function getPageTitle()
	{
		return "Account Creation Tool users";
	}
	
	function getPageName()
	{
		return "Users";
	}
	
	function isProtected()
	{
		return false;
	}
	
	function getUserList()
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
	
	function getUserDetail($userId)
	{
        $database = gGetDb();
        
        $user = User::getById($userId, $database);
        if($user == false)
        {
            return BootstrapSkin::displayAlertBox("User not found", "alert-error", "Error", true, false, true);
        }
        
        global $smarty;
		
        $activitySummary = $database->prepare("select coalesce(c.mail_desc, l.log_action) action, COUNT(*) count from acc_log l left join closes c on l.log_action = c.closes where l.log_user = :username group by action;");
        $activitySummary->execute(array(":username" => $user->getUsername()));
		$activitySummaryData = $activitySummary->fetchAll(PDO::FETCH_ASSOC);
        
        $smarty->assign("user", $user);
        $smarty->assign("activity", $activitySummaryData);
		
        $usersCreatedQuery = $database->prepare("SELECT log_time time, pend_name name, pend_id id FROM acc_log JOIN acc_pend ON pend_id = log_pend LEFT JOIN emailtemplate ON concat('Closed ', id) = log_action WHERE log_user = :username AND log_action LIKE 'Closed %' AND (oncreated = '1' OR log_action = 'Closed custom-y') ORDER BY log_time;");
        $usersCreatedQuery->execute(array(":username" => $user->getUsername()));
        $usersCreated = $usersCreatedQuery->fetchAll(PDO::FETCH_ASSOC);
        $smarty->assign("created", $usersCreated);
        
        $usersNotCreatedQuery = $database->prepare("SELECT log_time time, pend_name name, pend_id id FROM acc_log JOIN acc_pend ON pend_id = log_pend LEFT JOIN emailtemplate ON concat('Closed ', id) = log_action WHERE log_user = :username AND log_action LIKE 'Closed %' AND (oncreated = '0' OR log_action = 'Closed custom-n' OR log_action='Closed 0') ORDER BY log_time;");
        $usersNotCreatedQuery->execute(array(":username" => $user->getUsername()));
        $usersNotCreated = $usersNotCreatedQuery->fetchAll(PDO::FETCH_ASSOC);
        $smarty->assign("notcreated", $usersNotCreated);
        
        $accountLogQuery = $database->prepare("SELECT * FROM acc_log where log_pend = :userid AND log_action RLIKE '(Approved|Suspended|Declined|Promoted|Demoted|Renamed|fchange)';");
        $accountLogQuery->execute(array(":userid" => $user->getId()));
        $accountLog = $accountLogQuery->fetchAll(PDO::FETCH_ASSOC);
        $smarty->assign("accountlog", $accountLog);
        
		return $smarty->fetch("statistics/userdetail.tpl");
	}
	
	function requiresWikiDatabase()
	{
		return false;
	}
}
