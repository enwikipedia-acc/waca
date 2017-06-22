<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

use Waca\DataObjects\User;
use Waca\Exceptions\EnvironmentException;
use Waca\Exceptions\ReadableException;
use Waca\Helpers\BlacklistHelper;
use Waca\Helpers\FakeBlacklistHelper;
use Waca\Helpers\TypeAheadHelper;
use Waca\Providers\GlobalState\GlobalStateProvider;
use Waca\Router\IRequestRouter;
use Waca\Security\RoleConfiguration;
use Waca\Security\SecurityManager;
use Waca\Security\TokenManager;
use Waca\Tasks\ITask;
use Waca\Tasks\InternalPageBase;
use Waca\Tasks\PageBase;

/**
 * Application entry point.
 *
 * @package Waca
 */
class WebStart extends ApplicationBase
{
    /**
     * @var IRequestRouter $requestRouter The request router to use. Note that different entry points have different
     *                                    routers and hence different URL mappings
     */
    private $requestRouter;
    /**
     * @var bool $isPublic Determines whether to use public interface objects or internal interface objects
     */
    private $isPublic = false;

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
        PdoDatabase $notificationsDatabase = null
    ) {
        parent::setupHelpers($page, $siteConfiguration, $database, $notificationsDatabase);

        if ($page instanceof PageBase) {
            $page->setTokenManager(new TokenManager());

            if ($page instanceof InternalPageBase) {
                $page->setTypeAheadHelper(new TypeAheadHelper());

                $identificationVerifier = new IdentificationVerifier($page->getHttpHelper(), $siteConfiguration,
                    $database);
                $page->setIdentificationVerifier($identificationVerifier);

                $page->setSecurityManager(new SecurityManager($identificationVerifier, new RoleConfiguration()));

                if ($siteConfiguration->getTitleBlacklistEnabled()) {
                    $page->setBlacklistHelper(new FakeBlacklistHelper());
                }
                else {
                    $page->setBlacklistHelper(new BlacklistHelper($page->getHttpHelper(),
                        $siteConfiguration->getMediawikiWebServiceEndpoint()));
                }
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
            print Offline::getOfflineMessage($this->isPublic(), $ex->getMessage());
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
        set_exception_handler(array(ExceptionHandler::class, 'exceptionHandler'));
        set_error_handler(array(ExceptionHandler::class, 'errorHandler'), E_RECOVERABLE_ERROR);

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

            $currentUser = User::getCurrent($database);
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
