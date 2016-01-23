<?php
namespace Waca;

use Exception;
use InterfaceMessage;
use Smarty;
use User;
use Waca\Exceptions\AccessDeniedException;

abstract class PageBase
{
	/** @var array The callable route to be taken, as determined by the request router. */
	private $route = null;
	/** @var string The name of the route to use, as determined by the request router. */
	private $routeName = "main";
	/** @var Smarty */
	private $smarty;
	/** @var string Smarty template to display */
	private $template = "base.tpl";
	/** @var string HTML title. Currently unused. */
	private $htmlTitle;
	/** @var string Extra JavaScript to include at the end of the page's execution */
	private $tailScript;

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
				$content = $this->smarty->fetch($this->template);
				ob_clean();
				print($content);
				ob_flush();
			}
		}
		else {
			// Not allowed to access this resource.
			// Firstly, let's check if we're even logged in.
			if(User::getCurrent()->isCommunityUser()){
				// Not logged in, redirect to login page

			}
			else
			{
				// actual error here.
				throw new AccessDeniedException();
			}
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
	 * @param $name  string The name of the variable
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
	 * Include extra JavaScript at the end of the page's execution
	 *
	 * @param $script string JavaScript to include at the end of the page
	 */
	protected final function setTailScript($script)
	{
		$this->tailScript = $script;
	}

	/**
	 * Performs generic page setup actions
	 */
	private final function setupPage()
	{
		$this->smarty = new Smarty();
		$this->setUpSmartyVariables();
	}

	/**
	 * Sets up the variables used by the main Smarty base template.
	 *
	 * This list is getting kinda long.
	 */
	private final function setUpSmartyVariables()
	{
		global $baseurl, $wikiurl, $mediawikiScriptPath;

		$this->assign("currentUser", User::getCurrent());
		$this->assign("loggedIn", (!User::getCurrent()->isCommunityUser()));
		$this->assign("baseurl", $baseurl);
		$this->assign("wikiurl", $wikiurl);
		$this->assign("mediawikiScriptPath", $mediawikiScriptPath);

		// TODO: this isn't very mockable, and requires a database link.
		$siteNoticeText = InterfaceMessage::get(InterfaceMessage::SITENOTICE);
		$this->assign("siteNoticeText", $siteNoticeText);

		// TODO: this isn't mockable either, and has side effects if you don't have git
		$this->assign("toolversion", Environment::getToolVersion());

		// TODO: implement this somehow
		$this->assign("onlineusers", "");
		$this->assign("tailscript", $this->tailScript);
	}

	/**
	 * Performs final tasks needed before rendering the page.
	 */
	private final function finalisePage()
	{
		// TODO: session alerts, but how do they fit in?
		// We don't want to clear them unless we know they're definitely going to be displayed to the user (think
		// redirects, but at the same time, we can't clear them after page execution in case the user has displayed
		// more session alerts.
		$this->assign("alerts", array());

		$this->assign("htmlTitle", $this->htmlTitle);
	}
}