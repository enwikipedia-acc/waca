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

class BootstrapSkin
{
	/**
	 * Summary of $tagstack
	 * @var string[]
	 */
	private static $tagstack = array();

	/**
	 * Prints the public interface footer to the screen.
	 */
	public static function displayPublicFooter()
	{
		global $smarty;

		// close all declared open tags
		while (count(self::$tagstack) != 0) {
			echo array_pop(self::$tagstack);
		}

		$online = '';
		$smarty->assign("onlineusers", $online);
		$smarty->assign("tailscript", null);

		$smarty->display("footer.tpl");
	}
}
