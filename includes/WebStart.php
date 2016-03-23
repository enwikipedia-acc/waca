<?php
namespace Waca;

use ErrorException;
use Exception;
use Waca\DataObjects\User;
use Waca\Exceptions\EnvironmentException;
use Waca\Exceptions\ReadableException;
use Waca\Helpers\TypeAheadHelper;
use Waca\Providers\GlobalStateProvider;
use Waca\Router\IRequestRouter;
use Waca\Security\TokenManager;
use Waca\Tasks\InternalPageBase;
use Waca\Tasks\ITask;
use Waca\Tasks\PageBase;

/**
 * Internal application entry point.
 *
 * @package Waca
 */
class WebStart extends ApplicationBase
{
	/**
	 * @var IRequestRouter
	 */
	private $requestRouter;
	/** @var bool */
	private $isPublic;

	/**
	 * WebStart constructor.
	 *
	 * @param SiteConfiguration $configuration The site configuration
	 * @param IRequestRouter    $router        The request router to use
	 */
	public function __construct(SiteConfiguration $configuration, IRequestRouter $router)
	{
		parent::__construct($configuration);

		$this->requestRouter = $router;
	}

	/**
	 * @param ITask             $page
	 * @param SiteConfiguration $siteConfiguration
	 * @param PdoDatabase       $database
	 * @param PdoDatabase       $notificationsDatabase
	 *
	 * @return void
	 */
	protected function setupHelpers(
		ITask $page,
		SiteConfiguration $siteConfiguration,
		PdoDatabase $database,
		PdoDatabase $notificationsDatabase
	) {

		parent::setupHelpers($page, $siteConfiguration, $database, $notificationsDatabase);

		if ($page instanceof PageBase) {
			$identificationVerifier = new IdentificationVerifier($page->getHttpHelper(), $siteConfiguration, $database);
			$page->setIdentificationVerifier($identificationVerifier);

			$page->setTokenManager(new TokenManager());

			if ($page instanceof InternalPageBase) {
				$page->setTypeAheadHelper(new TypeAheadHelper());
			}
		}
	}

	/**
	 * Application entry point.
	 *
	 * Sets up the environment and runs the application, performing any global cleanup operations when done.
	 */
	public function run()
	{
		try {
			if ($this->setupEnvironment()) {
				$this->main();
			}
		}
		catch (EnvironmentException $ex) {
			ob_end_clean();
			print Offline::getOfflineMessage(false, $ex->getMessage());
		}
		catch (ReadableException $ex) {
			ob_end_clean();
			print $ex->getReadableError();
		}
		finally {
			$this->cleanupEnvironment();
		}
	}

	/**
	 * Global exception handler
	 *
	 * Smarty would be nice to use, but it COULD BE smarty that throws the errors.
	 * Let's build something ourselves, and hope it works.
	 *
	 * @param $exception
	 *
	 * @category Security-Critical - has the potential to leak data when exception is thrown.
	 */
	public static function exceptionHandler(Exception $exception)
	{
		/** @global $siteConfiguration SiteConfiguration */
		global $siteConfiguration;

		$errorDocument = <<<HTML
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8">
<title>Oops! Something went wrong!</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$siteConfiguration->getBaseUrl()}/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<style>
  body {
    padding-top: 60px;
  }
</style>
<link href="{$siteConfiguration->getBaseUrl()}/lib/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
</head><body><div class="container">
<h1>Oops! Something went wrong!</h1>
<p>We'll work on fixing this for you, so why not come back later?</p>
<p class="muted">If our trained monkeys ask, tell them this error ID: <code>$1$</code></p>
$2$
</div></body></html>
HTML;

		$errorData = self::getExceptionData($exception);
		$errorData['server'] = $_SERVER;
		$errorData['get'] = $_GET;
		$errorData['post'] = $_POST;

		$state = serialize($errorData);
		$errorId = sha1($state);

		// Save the error for later analysis
		file_put_contents($siteConfiguration->getErrorLog() . '/' . $errorId . '.log', $state);

		// clear and discard any content that's been saved to the output buffer
		if (ob_get_level() > 0) {
			ob_end_clean();
		}

		// push error ID into the document.
		$message = str_replace('$1$', $errorId, $errorDocument);

		if ($siteConfiguration->getDebuggingTraceEnabled()) {
			ob_start();
			var_dump($errorData);
			$textErrorData = ob_get_contents();
			ob_end_clean();

			$message = str_replace('$2$', $textErrorData, $message);
		}
		else {
			$message = str_replace('$2$', "", $message);
		}

		header('HTTP/1.1 500 Internal Server Error');

		// output the document
		print $message;
	}

	public static function errorHandler($err_severity, $err_msg, $err_file, $err_line)
	{
		// call into the main exception handler above
		throw new ErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
	}

	/**
	 * Environment setup
	 *
	 * This method initialises the tool environment. If the tool cannot be initialised correctly, it will return false
	 * and shut down prematurely.
	 *
	 * @return bool
	 * @throws EnvironmentException
	 */
	protected function setupEnvironment()
	{
		// initialise global exception handler
		set_exception_handler(array(self::class, 'exceptionHandler'));
		set_error_handler(array(self::class, 'errorHandler'), E_RECOVERABLE_ERROR);

		// start output buffering if necessary
		if (ob_get_level() === 0) {
			ob_start();
		}

		// initialise super-global providers
		WebRequest::setGlobalStateProvider(new GlobalStateProvider());

		if (Offline::isOffline()) {
			print Offline::getOfflineMessage($this->isPublic());
			ob_end_flush();

			return false;
		}

		// Call parent setup
		if (!parent::setupEnvironment()) {
			return false;
		}

		// Start up sessions
		Session::start();

		// Check the user is allowed to be logged in still. This must be before we call any user-loading functions and
		// get the current user cached.
		// I'm not sure if this function call being here is particularly a good thing, but it's part of starting up a
		// session I suppose.
		$this->checkForceLogout();

		// environment initialised!
		return true;
	}

	/**
	 * Main application logic
	 */
	protected function main()
	{
		// Get the right route for the request
		$page = $this->requestRouter->route();

		$siteConfiguration = $this->getConfiguration();
		$database = PdoDatabase::getDatabaseConnection('acc');

		if ($siteConfiguration->getIrcNotificationsEnabled()) {
			$notificationsDatabase = PdoDatabase::getDatabaseConnection('notifications');
		}
		else {
			// @todo federated table here?
			$notificationsDatabase = $database;
		}

		$this->setupHelpers($page, $siteConfiguration, $database, $notificationsDatabase);

		/* @todo Remove this global statement! It's here for User.php, which does far more than it should. */
		global $oauthHelper;
		$oauthHelper = $page->getOAuthHelper();

		/* @todo Remove this global statement! It's here for Request.php, which does far more than it should. */
		global $globalXffTrustProvider;
		$globalXffTrustProvider = $page->getXffTrustProvider();

		// run the route code for the request.
		$page->execute();
	}

	/**
	 * Any cleanup tasks should go here
	 *
	 * Note that we need to be very careful here, as exceptions may have been thrown and handled.
	 * This should *only* be for cleaning up, no logic should go here.
	 */
	protected function cleanupEnvironment()
	{
		// Clean up anything we splurged after sending the page.
		if (ob_get_level() > 0) {
			for ($i = ob_get_level(); $i > 0; $i--) {
				ob_end_clean();
			}
		}
	}

	/**
	 * @param Exception $exception
	 *
	 * @return null|array
	 */
	private static function getExceptionData($exception)
	{
		if ($exception == null) {
			return null;
		}

		return array(
			'exception' => get_class($exception),
			'message'   => $exception->getMessage(),
			'stack'     => $exception->getTraceAsString(),
			'previous'  => self::getExceptionData($exception->getPrevious()),
		);
	}

	private function checkForceLogout()
	{
		$database = PdoDatabase::getDatabaseConnection('acc');

		$sessionUserId = WebRequest::getSessionUserId();
		iF ($sessionUserId === null) {
			return;
		}

		// Note, User::getCurrent() caches it's result, which we *really* don't want to trigger.
		$currentUser = User::getById($sessionUserId, $database);

		if ($currentUser === false) {
			// Umm... this user has a session cookie with a userId set, but no user exists...
			Session::restart();
		}

		if ($currentUser->getForceLogout()) {
			Session::restart();

			$currentUser->setForceLogout(false);
			$currentUser->save();
		}
	}

	public function isPublic()
	{
		return $this->isPublic;
	}

	public function setPublic($isPublic)
	{
		$this->isPublic = $isPublic;
	}
}
