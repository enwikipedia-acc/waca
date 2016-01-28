<?php
namespace Waca;

use Exception;
use User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\NotIdentifiedException;
use Waca\Fragments\TemplateOutput;
use Waca\Helpers\HttpHelper;
use Waca\Helpers\Interfaces\IEmailHelper;
use Waca\Helpers\WikiTextHelper;

abstract class PageBase
{
	use TemplateOutput;
	/** @var array The callable route to be taken, as determined by the request router. */
	private $route = null;
	/** @var string The name of the route to use, as determined by the request router. */
	private $routeName = "main";
	/** @var string Smarty template to display */
	private $template = "base.tpl";
	/** @var string HTML title. Currently unused. */
	private $htmlTitle;
	/** @var IEmailHelper */
	private $emailHelper;
	/** @var SiteConfiguration */
	private $siteConfiguration;

	/** @var HttpHelper */
	private $httpHelper;

	/** @var WikiTextHelper */
	private $wikiTextHelper;

	/**
	 * Sets the route the request will take. Only should be called from the request router.
	 *
	 * @param $routeName string
	 * @throws Exception
	 * @category Security-Critical
	 */
	final public function setRoute($routeName)
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
	final public function execute()
	{
		if ($this->route === null) {
			throw new Exception("Request is unrouted.");
		}

		if ($this->siteConfiguration === null) {
			throw new Exception("Page has no configuration!");
		}

		$this->setupPage();

		// Security barrier.
		//
		// This code essentially doesn't care if the user is logged in or not, as the
		if ($this->getSecurityConfiguration()->allows(User::getCurrent())) {
			// We're allowed to run the page, so let's run it.
			$this->runPage();
		}
		else {
			// Not allowed to access this resource.
			// Firstly, let's check if we're even logged in.
			if (User::getCurrent()->isCommunityUser()) {
				// Not logged in, redirect to login page

				// TODO: return to current page? Possibly as a session var?
				$this->redirect("login");
				return;
			}
			else {
				// Decide whether this was a rights failure, or an identification failure.

				if ($this->getSiteConfiguration()->getForceIdentification()
					&& User::getCurrent()->isIdentified() != 1
				) {
					// Not identified
					throw new NotIdentifiedException();
				}
				else {
					// Nope, plain old access denied
					throw new AccessDeniedException();
				}
			}
		}
	}

	/**
	 * Tests the security barrier for a specified action.
	 *
	 * Intended to be used from within templates
	 *
	 * @param string $action
	 * @return boolean
	 * @category Security-Critical
	 */
	final public function barrierTest($action)
	{
		$tmpRouteName = $this->routeName;

		try {
			$this->routeName = $action;
			$allowed = $this->getSecurityConfiguration()->allows(User::getCurrent());
		}
		finally {
			$this->routeName = $tmpRouteName;
		}

		return $allowed;
	}

	/**
	 * @param IEmailHelper $emailHelper
	 */
	public function setEmailHelper($emailHelper)
	{
		$this->emailHelper = $emailHelper;
	}

	/**
	 * @return IEmailHelper
	 */
	public function getEmailHelper()
	{
		return $this->emailHelper;
	}

	/**
	 * Sets the site configuration object for this page
	 * @param $configuration
	 */
	public function setSiteConfiguration($configuration)
	{
		$this->siteConfiguration = $configuration;
	}

	/**
	 * Gets the site configuration object
	 *
	 * @return SiteConfiguration
	 */
	protected function getSiteConfiguration()
	{
		return $this->siteConfiguration;
	}

	/**
	 * @return HttpHelper
	 */
	public function getHttpHelper()
	{
		return $this->httpHelper;
	}

	/**
	 * @param HttpHelper $httpHelper
	 */
	public function setHttpHelper($httpHelper)
	{
		$this->httpHelper = $httpHelper;
	}

	/**
	 * @return WikiTextHelper
	 */
	public function getWikiTextHelper()
	{
		return $this->wikiTextHelper;
	}

	/**
	 * @param WikiTextHelper $wikiTextHelper
	 */
	public function setWikiTextHelper($wikiTextHelper)
	{
		$this->wikiTextHelper = $wikiTextHelper;
	}

	/**
	 * Main function for this page, when no specific actions are called.
	 * @return void
	 */
	abstract protected function main();

	/**
	 * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
	 * the return value from this function.
	 *
	 * If this page even supports actions, you will need to check the route
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	abstract protected function getSecurityConfiguration();

	/**
	 * Gets the name of the route that has been passed from the request router.
	 * @return string
	 */
	final protected function getRouteName()
	{
		return $this->routeName;
	}

	/**
	 * Sets the name of the template this page should display.
	 * @param string $name
	 */
	final protected function setTemplate($name)
	{
		$this->template = $name;
	}

	/**
	 * Sends the redirect headers to perform a GET at the new address.
	 *
	 * Also nullifies the set template so Smarty does not render it.
	 *
	 * @param string $path URL to redirect to
	 */
	protected function redirectUrl($path)
	{
		// 303 See Other = re-request at new address with a GET.
		header("HTTP/1.1 303 See Other");
		header("Location: $path");

		$this->setTemplate(null);
	}

	/**
	 * Sends the redirect headers to perform a GET at the destination page.
	 *
	 * Also nullifies the set template so Smarty does not render it.
	 *
	 * @param string      $page   The page to redirect requests to (as used in the UR)
	 * @param null|string $action The action to use on the page.
	 */
	protected function redirect($page, $action = null)
	{
		$pathInfo = array($this->getSiteConfiguration()->getBaseUrl() . "/internal.php");

		$pathInfo[1] = $page;

		if ($action !== null) {
			$pathInfo[2] = $action;
		}

		$url = implode("/", $pathInfo);
		$this->redirectUrl($url);
	}

	/**
	 * Performs generic page setup actions
	 */
	final private function setupPage()
	{
		$this->setUpSmarty();
	}

	/**
	 * Performs final tasks needed before rendering the page.
	 */
	final private function finalisePage()
	{
		// TODO: session alerts, but how do they fit in?
		// We don't want to clear them unless we know they're definitely going to be displayed to the user (think
		// redirects, but at the same time, we can't clear them after page execution in case the user has displayed
		// more session alerts.
		$this->assign("alerts", array());

		$this->assign("htmlTitle", $this->htmlTitle);
	}

	/**
	 * Runs the page logic as routed by the RequestRouter
	 *
	 * Only should be called after a security barrier! That means only from execute().
	 */
	final private function runPage()
	{
		// run the page code
		call_user_func($this->route);

		// run any finalisation code needed before we send the output to the browser.
		$this->finalisePage();

		// Check we have a template to use!
		if ($this->template !== false) {
			$content = $this->fetchTemplate($this->template);
			ob_clean();
			print($content);
			ob_flush();
			return;
		}
	}
}