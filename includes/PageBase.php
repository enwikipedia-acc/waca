<?php
namespace Waca;

use Exception;
use Smarty;
use User;

abstract class PageBase
{
	/** @var array The callable route to be taken, as determined by the request router. */
	private $route = null;

	/** @var string The name of the route to use, as determined by the request router. */
	private $routeName = "main";

	/** @var Smarty */
	private $smarty;

	/** @var string Smarty template to display */
	private $template;

	/** @var string HTML title. Currently unused. */
	private $htmlTitle;

	/**
	 * Sets the route the request will take. Only should be called from the request router.
	 *
	 * @param $routeName string
	 * @throws Exception
	 * @category Security-Critical
	 */
	public final function setRoute($routeName)
	{
		// Test the new route is callable before adopting it.
		$proposedRoute = array($this, $routeName);
		if (!is_callable($proposedRoute)) {
			throw new Exception("Proposed route '$routeName' is not callable.");
		}

		// Adopt the new route
		$this->route = $proposedRoute;
		$this->routeName = $routeName;
	}

	/**
	 * Runs the page code
	 *
	 * @throws Exception
	 * @category Security-Critical
	 */
	public final function execute()
	{
		if ($this->route === null) {
			throw new Exception("Request is unrouted.");
		}

		$this->setupPage();

		// Security barrier
		if ($this->getSecurityConfiguration()->allows(User::getCurrent())) {

			call_user_func($this->route);

			$this->finalisePage();

			// Check we have a template to use!
			if ($this->template !== false) {
				$content = $this->smarty->fetch("base.tpl");
				ob_clean();
				print($content);
				ob_flush();
			}
		}
		else {
			// TODO: Headers etc
			throw new Exception("403 error, security config disallows");
		}
	}

	/**
	 * Main function for this page, when no specific actions are called.
	 */
	protected abstract function main();

	/**
	 * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
	 * the return value from this function.
	 *
	 * If this page even supports actions, you will need to check the route
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	protected abstract function getSecurityConfiguration();

	/**
	 * Gets the name of the route that has been passed from the request router.
	 * @return string
	 */
	protected final function getRouteName()
	{
		return $this->routeName;
	}

	/**
	 * @param $name string The name of the variable
	 * @param $value mixed The value to assign
	 */
	protected final function assign($name, $value)
	{
		$this->smarty->assign($name, $value);
	}

	/**
	 * Sets the name of the template this page should display.
	 * @param $name string
	 */
	protected final function setTemplate($name)
	{
		$this->template = $name;
	}

	/**
	 * Performs generic page setup actions
	 */
	private final function setupPage()
	{
		global $baseurl, $wikiurl, $mediawikiScriptPath;

		$this->smarty = new Smarty();

		$this->assign("currentUser", User::getCurrent());
		$this->assign("baseurl", $baseurl);
		$this->assign("wikiurl", $wikiurl);
		$this->assign("mediawikiScriptPath", $mediawikiScriptPath);
	}

	/**
	 * Performs final tasks needed before rendering the page.
	 */
	private final function finalisePage()
	{
		$this->assign("htmlTitle", $this->htmlTitle);
	}
}