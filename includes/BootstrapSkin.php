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
	 * Summary of displayPublicHeader
	 */
	public static function displayPublicHeader()
	{
		global $smarty;
		$smarty->display("header-external.tpl");
	}

	/**
	 * Summary of displayInternalHeader
	 */
	public static function displayInternalHeader()
	{
		// userid
		// username
		// sitenotice
		global $smarty, $session;

		$userid = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
		$user = isset($_SESSION['user']) ? $_SESSION['user'] : "";
		$sitenotice = InterfaceMessage::get(InterfaceMessage::SITENOTICE);
		$smarty->assign("userid", $userid);
		$smarty->assign("username", $user);
		$smarty->assign("sitenotice", $sitenotice);
		$smarty->assign("alerts", SessionAlert::retrieve());
		$smarty->display("header-internal.tpl");

		if ($userid != 0) {
			User::getCurrent()->touchLastLogin();

			$session->forceLogout($_SESSION['userID']);
		}
	}

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


	/**
	 * Prints the internal interface footer to the screen.
	 *
	 * @param string|null $tailscript JavaScript to append to the page, usually so it can call jQuery
	 * @throws Exception
	 */
	public static function displayInternalFooter($tailscript = null)
	{
		global $smarty;

		// close all declared open tags
		while (count(self::$tagstack) != 0) {
			echo array_pop(self::$tagstack);
		}

		$last5min = time() - 300;
		$last5mins = date("Y-m-d H:i:s", $last5min);

		$database = gGetDb();
		$statement = $database->prepare("SELECT * FROM user WHERE lastactive > :lastfive;");
		$statement->execute(array(":lastfive" => $last5mins));
		$resultSet = $statement->fetchAll(PDO::FETCH_CLASS, "User");
		$resultSetCount = count($resultSet);

		$creators = implode(
			", ",
			array_map(
				function($arg)
				{
					/** @var User $arg */
					return
						"<a href=\"statistics.php?page=Users&amp;user="
						. $arg->getId()
						. "\">"
						. htmlentities($arg->getUsername())
						. "</a>";
				},
				$resultSet
			)
		);

		// not equal to one, as zero uses the plural form too.
		if ($resultSetCount != 1) {
			$onlinemessage = $resultSetCount . " Account Creators currently online (past 5 minutes): $creators";
		}
		else {
			$onlinemessage = $resultSetCount . " Account Creator currently online (past 5 minutes): $creators";
		}

		$online = '<p class="span6 text-right"><small>' . $onlinemessage . '</small></p>';

		if (isset($_SESSION['user'])) {
			$smarty->assign("onlineusers", $online);
		}
		else {
			$emptystring = "";
			$smarty->assign("onlineusers", $emptystring);
		}

		$smarty->assign("tailscript", $tailscript);

		$smarty->display("footer.tpl");
	}

	/**
	 * Summary of displayAlertBox
	 * @param string $message   Message to show
	 * @param string $type      Alert type - use bootstrap css class
	 * @param string $header    the header of the box
	 * @param bool   $block     Whether to make this a block or not
	 * @param bool   $closeable add a close button
	 * @param bool   $return    return the content as a string, or display it.
	 * @param bool   $centre    centre the box in the page, like a dialog.
	 * @return null|string
	 * @throws Exception
	 * @throws SmartyException
	 */
	public static function displayAlertBox(
		$message,
		$type = "",
		$header = "",
		$block = false,
		$closeable = true,
		$return = false,
		$centre = false
		) {
		global $smarty;
		$smarty->assign("alertmessage", $message);
		$smarty->assign("alerttype", $type);
		$smarty->assign("alertheader", $header);
		$smarty->assign("alertblock", $block);
		$smarty->assign("alertclosable", $closeable);

		$returnData = $smarty->fetch("alert.tpl");

		if ($centre) {
			$returnData = '<div class="row-fluid"><div class="span8 offset2">' . $returnData . '</div></div>';
		}

		if ($return) {
			return $returnData;
		}
		else {
			echo $returnData;
			return null;
		}
	}

	/**
	 * Prints the account request form to the screen.
	 * @deprecated
	 */
	public static function displayRequestForm( )
	{
		global $smarty;
		$smarty->display("request-form.tpl");
	}

	/**
	 * Push a close tag onto the tag stack.
	 *
	 * This will ensure that all tags are closed when you show the footer.
	 *
	 * @param string $tag The closing tag to display
	 */
	public static function pushTagStack($tag)
	{
		array_push(self::$tagstack, $tag);
	}

	/**
	 * Remove an item from the tagstack
	 * @return string
	 */
	public static function popTagStack()
	{
		return array_pop(self::$tagstack);
	}

	public static function displayAccessDenied()
	{
		self::displayAlertBox(
			"I'm sorry, but you do not have permission to access this page.",
			"alert-error",
			"Access Denied",
			true,
			false);
	}
}
