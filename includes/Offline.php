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
use Waca\Environment;

/**
 * Handles the tool offline messages
 */
class Offline
{
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
	 *
	 * @param bool $external
	 * @param null $message
	 *
	 * @return string
	 */
	public static function getOfflineMessage($external, $message = null)
	{
		global $dontUseDbCulprit, $dontUseDbReason, $baseurl;

		$smarty = new Smarty();
		$smarty->assign("baseurl", $baseurl);
		$smarty->assign("toolversion", Environment::getToolVersion());

		header("HTTP/1.1 503 Service Unavailable");

		if ($external) {
			return $smarty->fetch("offline/external.tpl");
		}
		else {
			$hideCulprit = true;

			// Use the provided message if possible
			if ($message === null) {
				$hideCulprit = false;
				$message = $dontUseDbReason;
			}

			$smarty->assign("hideCulprit", $hideCulprit);
			$smarty->assign("dontUseDbCulprit", $dontUseDbCulprit);
			$smarty->assign("dontUseDbReason", $message);
			$smarty->assign("alerts", array());

			return $smarty->fetch("offline/internal.tpl");
		}
	}
}
