<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca;

use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\Exceptions\EnvironmentException;
use Waca\Exceptions\ReadableException;
use Waca\Helpers\BlacklistHelper;
use Waca\Helpers\FakeBlacklistHelper;
use Waca\Helpers\TypeAheadHelper;
use Waca\Providers\GlobalState\GlobalStateProvider;
use Waca\Router\IRequestRouter;
use Waca\Security\ContentSecurityPolicyManager;
use Waca\Security\DomainAccessManager;
use Waca\Security\RoleConfiguration;
use Waca\Security\SecurityManager;
use Waca\Security\TokenManager;
use Waca\Security\UserAccessLoader;
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
    private bool $isPublic = false;

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
     *
     * @return void
     */
    protected function setupHelpers(
        ITask $page,
        SiteConfiguration $siteConfiguration,
        PdoDatabase $database
    ) {
        parent::setupHelpers($page, $siteConfiguration, $database);

        if ($page instanceof PageBase) {
            $page->setTokenManager(new TokenManager());
            $page->setCspManager(new ContentSecurityPolicyManager($siteConfiguration));

            if ($page instanceof InternalPageBase) {
                $page->setTypeAheadHelper(new TypeAheadHelper());

                $httpHelper = $page->getHttpHelper();

                $userAccessLoader = new UserAccessLoader();
                $domainAccessManager = new DomainAccessManager($userAccessLoader);

                $identificationVerifier = new IdentificationVerifier($httpHelper, $siteConfiguration, $database);

                $page->setSecurityManager(new SecurityManager($identificationVerifier, new RoleConfiguration(), $userAccessLoader));
                $page->setDomainAccessManager($domainAccessManager);

                if ($siteConfiguration->getTitleBlacklistEnabled()) {
                    $page->setBlacklistHelper(new BlacklistHelper($httpHelper, $database, $siteConfiguration));
                }
                else {
                    $page->setBlacklistHelper(new FakeBlacklistHelper());
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
            print Offline::getOfflineMessage($this->isPublic(), $this->getConfiguration(), $ex->getMessage());
        }
            /** @noinspection PhpRedundantCatchClauseInspection */
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

        if (Offline::isOffline($this->getConfiguration())) {
            print Offline::getOfflineMessage($this->isPublic(), $this->getConfiguration());
            ob_end_flush();

            return false;
        }

        // Call parent setup
        if (!parent::setupEnvironment()) {
            return false;
        }

        // Start up sessions
        ini_set('session.cookie_path', $this->getConfiguration()->getCookiePath());
        ini_set('session.name', $this->getConfiguration()->getCookieSessionName());
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
        $database = PdoDatabase::getDatabaseConnection($this->getConfiguration());

        $this->setupHelpers($page, $siteConfiguration, $database);

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
        $database = PdoDatabase::getDatabaseConnection($this->getConfiguration());

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

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
    }
}
