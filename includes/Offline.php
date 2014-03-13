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

class Offline 
{
    public static function check($external)
    {
        global $smarty, $dontUseDb, $dontUseDbCulprit, $dontUseDbReason;
        
		if ($dontUseDb) 
        {
			if ($external) 
            {
				$smarty->display("offline/external.tpl");
            } 
            else 
            {
                $smarty->assign("dontUseDbCulprit", $dontUseDbCulprit);
                $smarty->assign("dontUseDbReason", $dontUseDbReason);
                $smarty->assign("alerts", array());
				$smarty->display("offline/internal.tpl");
			}
            
			die();
		}
    }
}