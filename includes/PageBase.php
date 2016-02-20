<?php
namespace Waca;

use Exception;
use IAntiSpoofProvider;
use ILocationProvider;
use IRDnsProvider;
use IXffTrustProvider;
use PdoDatabase;
use SessionAlert;
use TransactionException;
use User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\NotIdentifiedException;
use Waca\Fragments\TemplateOutput;
use Waca\Helpers\HttpHelper;
use Waca\Helpers\Interfaces\IEmailHelper;
use Waca\Helpers\Interfaces\IOAuthHelper;
use Waca\Helpers\Interfaces\ITypeAheadHelper;
use Waca\Helpers\WikiTextHelper;

abstract class PageBase
{
	use TemplateOutput;
	/** @var string The name of the route to use, as determined by the request router. */
	private $routeName = null;
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
	/** @var ILocationProvider */
	private $locationProvider;
	/** @var IXffTrustProvider */
	private $xffTrustProvider;
	/** @var IRDnsProvider */
	private $rdnsProvider;
	/** @var IAntiSpoofProvider */
	private $antiSpoofProvider;
	/** @var bool Determines if the page is a redirect or not */
	private $isRedirecting = false;
	/** @var PdoDatabase */
	private $database;
	/** @var array Queue of headers to be sent on successful completion */
	private $headerQueue = array();
	/** @var ITypeAheadHelper */
	private $typeAheadHelper;
	/** @var IOAuthHelper */
	private $oauthHelper;

	/**
	 * Sets the route the request will take. Only should be called from the request router.
	 *
	 * @param $routeName string
	 *
	 * @throws Exception
	 * @category Security-Critical
	 */
	final public function setRoute($routeName)
	{
		// Test the new route is callable before adopting it.
		if (!is_callable(array($this, $routeName))) {
			throw new Exception("Proposed route '$routeName' is not callable.");
		}

		// Adopt the new route
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
		if ($this->routeName === null) {
			throw new Exception("Request is unrouted.");
		}

		if ($this->siteConfiguration === null) {
			throw new Exception("Page has no configuration!");
		}

		$this->setupPage();

		// Get the current security configuration
		$securityConfiguration = $this->getSecurityConfiguration();
		if ($securityConfiguration === null) {
			// page hasn't been written properly.
			throw new AccessDeniedException();
		}

		$currentUser = User::getCurrent($this->getDatabase());

		// Security barrier.
		//
		// This code essentially doesn't care if the user is logged in or not, as the
		if ($securityConfiguration->allows($currentUser)) {
			// We're allowed to run the page, so let's run it.
			$this->runPage();
		}
		else {
			$this->handleAccessDenied();

			// Send the headers
			foreach ($this->headerQueue as $item) {
				header($item);
			}
		}
	}

	/**
	 * Performs generic page setup actions
	 */
	final private function setupPage()
	{
		$this->setUpSmarty();
	}

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
	 * Runs the page logic as routed by the RequestRouter
	 *
	 * Only should be called after a security barrier! That means only from execute().
	 */
	final private function runPage()
	{
		// initialise a database transaction
		if (!$this->database->beginTransaction()) {
			throw new Exception('Failed to start transaction on primary database.');
		}

		try {
			// run the page code
			$this->{$this->routeName}();

			$this->database->commit();
		}
		catch (TransactionException $ex) {
			$this->database->rollBack();
			throw $ex;
		}
		catch (ApplicationLogicException $ex) {
			// it's an application logic exception, so nothing went seriously wrong with the site. We can use the
			// standard templating system for this.

			// Firstly, let's undo anything that happened to the database.
			$this->database->rollBack();

			// Reset smarty
			$this->setUpSmarty();

			// Set the template
			$this->setTemplate("exception/application-logic.tpl");
			$this->assign('message', $ex->getMessage());

			// Force this back to false
			$this->isRedirecting = false;
			$this->headerQueue = array();
		}
		finally {
			// Catch any hanging on transactions
			if ($this->database->hasActiveTransaction()) {
				$this->database->rollBack();
			}
		}

		// run any finalisation code needed before we send the output to the browser.
		$this->finalisePage();

		// Send the headers
		foreach ($this->headerQueue as $item) {
			header($item);
		}

		// Check we have a template to use!
		if ($this->template !== null) {
			$content = $this->fetchTemplate($this->template);
			ob_clean();
			print($content);
			ob_flush();

			return;
		}
	}

	/**
	 * Performs final tasks needed before rendering the page.
	 */
	final private function finalisePage()
	{
		if ($this->isRedirecting) {
			$this->template = null;

			return;
		}

		if (User::getCurrent($this->getDatabase())->isNew()) {
			$registeredSuccessfully = new SessionAlert(
				'Your request will be reviewed soon by a tool administrator, and you\'ll get an email informing you of the decision. You won\'t be able to access most of the tool until then.',
				'Account Requested!', 'alert-success', false);
			SessionAlert::append($registeredSuccessfully);
		}

		// If we're actually displaying content, we want to add the session alerts here!
		$this->assign("alerts", SessionAlert::getAlerts());
		SessionAlert::clearAlerts();

		$this->assign("htmlTitle", $this->htmlTitle);

		$this->assign("typeAheadBlock", $this->typeAheadHelper->getTypeAheadScriptBlock());
	}

	protected function handleAccessDenied()
	{
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

	/**
	 * Sends the redirect headers to perform a GET at the destination page.
	 *
	 * Also nullifies the set template so Smarty does not render it.
	 *
	 * @param string      $page   The page to redirect requests to (as used in the UR)
	 * @param null|string $action The action to use on the page.
	 * @param null|array  $parameters
	 */
	final protected function redirect($page = '', $action = null, $parameters = null)
	{
		$pathInfo = array($this->getSiteConfiguration()->getBaseUrl() . "/internal.php");

		$pathInfo[1] = $page;

		if ($action !== null) {
			$pathInfo[2] = $action;
		}

		$url = implode("/", $pathInfo);

		if (is_array($parameters) && count($parameters) > 0) {
			$url .= '?' . http_build_query($parameters);
		}

		$this->redirectUrl($url);
	}

	/**
	 * Gets the site configuration object
	 *
	 * @return SiteConfiguration
	 */
	final protected function getSiteConfiguration()
	{
		return $this->siteConfiguration;
	}

	/**
	 * Sets the site configuration object for this page
	 *
	 * @param $configuration
	 */
	final public function setSiteConfiguration($configuration)
	{
		$this->siteConfiguration = $configuration;
	}

	/**
	 * Sends the redirect headers to perform a GET at the new address.
	 *
	 * Also nullifies the set template so Smarty does not render it.
	 *
	 * @param string $path URL to redirect to
	 */
	final protected function redirectUrl($path)
	{
		// 303 See Other = re-request at new address with a GET.
		$this->headerQueue[] = "HTTP/1.1 303 See Other";
		$this->headerQueue[] = "Location: $path";

		$this->setTemplate(null);
		$this->isRedirecting = true;
	}

	/**
	 * Sets the name of the template this page should display.
	 *
	 * @param string $name
	 *
	 * @throws Exception
	 */
	final protected function setTemplate($name)
	{
		if ($this->isRedirecting) {
			throw new Exception('This page has been set as a redirect, no template can be displayed!');
		}

		$this->template = $name;
	}

	/**
	 * Tests the security barrier for a specified action.
	 *
	 * Intended to be used from within templates
	 *
	 * @param string $action
	 *
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
	 * @return IEmailHelper
	 */
	final public function getEmailHelper()
	{
		return $this->emailHelper;
	}

	/**
	 * @param IEmailHelper $emailHelper
	 */
	final public function setEmailHelper($emailHelper)
	{
		$this->emailHelper = $emailHelper;
	}

	/**
	 * @return HttpHelper
	 */
	final public function getHttpHelper()
	{
		return $this->httpHelper;
	}

	/**
	 * @param HttpHelper $httpHelper
	 */
	final public function setHttpHelper($httpHelper)
	{
		$this->httpHelper = $httpHelper;
	}

	/**
	 * @return WikiTextHelper
	 */
	final public function getWikiTextHelper()
	{
		return $this->wikiTextHelper;
	}

	/**
	 * @param WikiTextHelper $wikiTextHelper
	 */
	final public function setWikiTextHelper($wikiTextHelper)
	{
		$this->wikiTextHelper = $wikiTextHelper;
	}

	/**
	 * @return ILocationProvider
	 */
	final public function getLocationProvider()
	{
		return $this->locationProvider;
	}

	/**
	 * @param ILocationProvider $locationProvider
	 */
	final public function setLocationProvider(ILocationProvider $locationProvider)
	{
		$this->locationProvider = $locationProvider;
	}

	/**
	 * @return IXffTrustProvider
	 */
	final public function getXffTrustProvider()
	{
		return $this->xffTrustProvider;
	}

	/**
	 * @param IXffTrustProvider $xffTrustProvider
	 */
	final public function setXffTrustProvider(IXffTrustProvider $xffTrustProvider)
	{
		$this->xffTrustProvider = $xffTrustProvider;
	}

	/**
	 * @return IRDnsProvider
	 */
	final public function getRdnsProvider()
	{
		return $this->rdnsProvider;
	}

	/**
	 * @param IRDnsProvider $rdnsProvider
	 */
	public function setRdnsProvider($rdnsProvider)
	{
		$this->rdnsProvider = $rdnsProvider;
	}

	/**
	 * @return IAntiSpoofProvider
	 */
	public function getAntiSpoofProvider()
	{
		return $this->antiSpoofProvider;
	}

	/**
	 * @param IAntiSpoofProvider $antiSpoofProvider
	 */
	public function setAntiSpoofProvider($antiSpoofProvider)
	{
		$this->antiSpoofProvider = $antiSpoofProvider;
	}

	/**
	 * Gets the name of the route that has been passed from the request router.
	 * @return string
	 */
	final public function getRouteName()
	{
		return $this->routeName;
	}

	final public function getDatabase()
	{
		return $this->database;
	}

	final public function setDatabase($database)
	{
		$this->database = $database;
	}

	/**
	 * @return ITypeAheadHelper
	 */
	public function getTypeAheadHelper()
	{
		return $this->typeAheadHelper;
	}

	/**
	 * @param ITypeAheadHelper $typeAheadHelper
	 */
	public function setTypeAheadHelper(ITypeAheadHelper $typeAheadHelper)
	{
		$this->typeAheadHelper = $typeAheadHelper;
	}

	/**
	 * @return IOAuthHelper
	 */
	public function getOAuthHelper()
	{
		return $this->oauthHelper;
	}

	/**
	 * @param IOAuthHelper $oauthHelper
	 */
	public function setOAuthHelper($oauthHelper)
	{
		$this->oauthHelper = $oauthHelper;
	}

	/**
	 * Main function for this page, when no specific actions are called.
	 * @return void
	 */
	abstract protected function main();

	/**
	 * @param string $title
	 */
	final protected function setHtmlTitle($title)
	{
		$this->htmlTitle = $title;
	}
}