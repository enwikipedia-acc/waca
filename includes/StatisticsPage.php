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

abstract class StatisticsPage
{
	/**
	 * Creates a statistics page.
	 *
	 * @param $pageName string Name of the page
	 * @return StatisticsPage Object of type dependant on the name specified.
	 */
	public static function Create($pageName)
	{
		// calculate the name of the statistics page
		$statsPage = "Stats" . $pageName;

		global $filepath;
		// check the stats page definition exists...
		if (file_exists($filepath . "/includes/statistics/Stats" . $pageName . ".php")) {
		// and include it.
			require_once($filepath . "/includes/statistics/Stats" . $pageName . ".php");
		}
		else {
			// class def doesn't exist: error
			die("Unknown statistics page: " . $statsPage);
		}

		// ok, so the file where the class def should be exists, but we need to check the class
		// itself exists.
		if (class_exists($statsPage)) {
			// the class exists, all is ok.

			// create the stats page object
			$object = new $statsPage;

			// check the newly created object has inherits from StatisticsPage class
			if (get_parent_class($object) == "StatisticsPage") {
				// all is good, return the new statistics page object
				return $object;
			}
			else {
				// oops. this is our class, named correctly, but it's a bad definition.
				die("Unrecognised statistics page definition.");
			}
		}
		else {
			// file exists, but no definition of the class
			die("No definition for statistics page: " . $statsPage);
		}
	}

	/**
	 * Abstract method provides the content of the statistics page
	 * @return string content of stats page.
	 */
	abstract protected function execute();

	/**
	 * Returns the title of the page (initial header, and name in menu)
	 * @return string
	 */
	abstract public function getPageTitle();

	/**
	 * Returns the name of the page (used in urls, and class defs)
	 * @return string
	 */
	abstract public function getPageName();

	/**
	 * Determines if the stats page is only available to logged-in users, or everyone.
	 * @return bool
	 */
	abstract public function isProtected();

	/**
	 * Determines if the statistics page requires the wiki database. Defaults to true
	 * @return bool
	 */
	public function requiresWikiDatabase()
	{
		return true;
	}

	/**
	 * Determines if the statistics page requires a simple HTML environment. Defaults to true
	 * @return bool
	 */
	public function requiresSimpleHtmlEnvironment()
	{
		return true;
	}

	/**
	 * Determines if the statistics page should be hidden from the main menu. Defaults to false.
	 * @return boolean
	 */
	public function hideFromMenu()
	{
		return false;
	}

	/**
	 * Shows the statistics page.
	 */
	public function Show()
	{
		// Get the needed objects.

		// fetch and show page header
		global $dontUseWikiDb;

		BootstrapSkin::displayInternalHeader();

		if ($this->requiresWikiDatabase() && ($dontUseWikiDb == 1)) {
// wiki database unavailable, don't show stats page
			BootstrapSkin::displayAlertBox("This statistics page is currently unavailable.", "alert-error", "Database unavailable", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}

		// wiki database available OR stats page doesn't need wiki database

		// check protection level
		if ($this->isProtected()) {
			if (User::getCurrent()->isCommunityUser()) {
				showlogin();
				BootstrapSkin::displayInternalFooter();
				die();
			}

			$session = new session();
			$session->checksecurity();
		}

		// not protected or access allowed
		echo '<div class="page-header"><h1>' . $this->getPageTitle() . '</h1></div>';

		if ($this->requiresSimpleHtmlEnvironment()) {
			echo '<div class="row-fluid"><div class="span12">';
			BootstrapSkin::pushTagStack("</div>");
			BootstrapSkin::pushTagStack("</div>");
		}

		echo $this->execute();

		// Display the footer of the interface.
		BootstrapSkin::displayInternalFooter();
	}
}
