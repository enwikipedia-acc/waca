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
if (!defined("ACC")) {
	die();
} // Invalid entry point

class StatsInactiveUsers extends StatisticsPage
{
	function execute()
	{
        global $smarty;
		global $tsSQL, $baseurl, $session;
		
		if( isset( $_SESSION['user'] ) ) {
			$sessionuser = $_SESSION['user'];
		} else {
			$sessionuser = "";
		}
		
		$showImmune = false;
		if(isset($_GET['showimmune']))
		{
			$showImmune = true;
		}
        $smarty->assign("showImmune", $showImmune);
		
        $inactiveUsers = User::getAllInactive(gGetDb());
        
        $smarty->assign("inactiveUsers", $inactiveUsers);
        
        return $smarty->fetch("statistics/inactiveusers.tpl");        
	}
    
	function getPageName()
	{
		return "InactiveUsers";
	}
	
    function getPageTitle()
	{
		return "Inactive tool users";
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