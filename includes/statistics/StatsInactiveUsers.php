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

class StatsInactiveUsers extends StatisticsPage
{
	protected function execute()
	{
        global $smarty;
        		
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
    
	public function getPageName()
	{
		return "InactiveUsers";
	}
	
    public function getPageTitle()
	{
		return "Inactive tool users";
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