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

/**
 * Handles the tool offline messages
 */
class Offline
{
	/**
	 * Summary of check
	 * @param bool $external External interface
	 * @deprecated Do checking within the entry point.
	 */
	public static function check($external)
	{
		global $smarty, $dontUseDb, $dontUseDbCulprit, $dontUseDbReason;

		if ($dontUseDb) {
			if ($external) {
				$smarty->display("offline/external.tpl");
			}
			else {
				$smarty->assign("dontUseDbCulprit", $dontUseDbCulprit);
				$smarty->assign("dontUseDbReason", $dontUseDbReason);
				$smarty->assign("alerts", array());
				$smarty->display("offline/internal.tpl");
			}

			die();
		}
	}

	/**
	 * Determines if the tool is offline
	 * @return bool
	 */
	public static function isOffline()
	{
		global $dontUseDb;

		return (bool)$dontUseDb;
	}

	/**
	 * Gets the offline message
	 * @param bool $external
	 * @return string
	 */
	public static function getOfflineMessage($external)
	{
		global $smarty, $dontUseDbCulprit, $dontUseDbReason;

		if ($external) {
			return $smarty->fetch("offline/external.tpl");
		}
		else {
			$smarty->assign("dontUseDbCulprit", $dontUseDbCulprit);
			$smarty->assign("dontUseDbReason", $dontUseDbReason);
			$smarty->assign("alerts", array());
			return $smarty->fetch("offline/internal.tpl");
		}
	}
}
