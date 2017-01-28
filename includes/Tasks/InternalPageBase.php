<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tasks;

use Exception;
use PDO;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\NotIdentifiedException;
use Waca\Fragments\NavigationMenuAccessControl;
use Waca\Helpers\Interfaces\IBlacklistHelper;
use Waca\IdentificationVerifier;
use Waca\Helpers\Interfaces\ITypeAheadHelper;
use Waca\Security\SecurityManager;
use Waca\WebRequest;

abstract class InternalPageBase extends PageBase
{
    use NavigationMenuAccessControl;

    /** @var IdentificationVerifier */
    private $identificationVerifier;
    /** @var ITypeAheadHelper */
    private $typeAheadHelper;
    /** @var SecurityManager */
    private $securityManager;
    /** @var IBlacklistHelper */
    private $blacklistHelper;

    /**
     * @return ITypeAheadHelper
     */
    public function getTypeAheadHelper()
    {
        return $this->typeAheadHelper;
    }

    /**
     * Sets up the internal IdentificationVerifier instance.  Intended to be called from WebStart::setupHelpers().
     *
     * @param IdentificationVerifier $identificationVerifier
     *
     * @return void
     */
    public function setIdentificationVerifier(IdentificationVerifier $identificationVerifier)
    {
        $this->identificationVerifier = $identificationVerifier;
    }

    /**
     * @param ITypeAheadHelper $typeAheadHelper
     */
    public function setTypeAheadHelper(ITypeAheadHelper $typeAheadHelper)
    {
        $this->typeAheadHelper = $typeAheadHelper;
    }

    /**
     * Runs the page code
     *
     * @throws Exception
     * @category Security-Critical
     */
    final public function execute()
    {
        if ($this->getRouteName() === null) {
            throw new Exception("Request is unrouted.");
        }

        if ($this->getSiteConfiguration() === null) {
            throw new Exception("Page has no configuration!");
        }

        $this->setupPage();

        $this->touchUserLastActive();

        $currentUser = User::getCurrent($this->getDatabase());

        // Hey, this is also a security barrier, in addition to the below. Separated out for readability.
        if (!$this->isProtectedPage()) {
            // This page is /not/ a protected page, as such we can just run it.
            $this->runPage();

            return;
        }

        // Security barrier.
        //
        // This code essentially doesn't care if the user is logged in or not, as the security manager hides all that
        // away for us
        $securityResult = $this->getSecurityManager()->allows(get_called_class(), $this->getRouteName(), $currentUser);
        if ($securityResult === SecurityManager::ALLOWED) {
            // We're allowed to run the page, so let's run it.
            $this->runPage();
        }
        else {
            $this->handleAccessDenied($securityResult);

            // Send the headers
            $this->sendResponseHeaders();
        }
    }

    /**
     * Performs final tasks needed before rendering the page.
     */
    final public function finalisePage()
    {
        parent::finalisePage();

        $this->assign('typeAheadBlock', $this->getTypeAheadHelper()->getTypeAheadScriptBlock());

        $database = $this->getDatabase();

        $currentUser = User::getCurrent($database);
        if (!$currentUser->isCommunityUser()) {
            $sql = 'SELECT * FROM user WHERE lastactive > DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL 5 MINUTE);';
            $statement = $database->query($sql);
            $activeUsers = $statement->fetchAll(PDO::FETCH_CLASS, User::class);
            $this->assign('onlineusers', $activeUsers);
        }

        $this->setupNavMenuAccess($currentUser);
    }

    /**
     * Configures whether the page respects roles or not. You probably want this to return true.
     *
     * Set to false for public pages. You probably want this to return true.
     *
     * This defaults to true unless you explicitly set it to false. Setting it to false means anybody can do anything
     * on this page, so you probably want this to return true.
     *
     * @return bool
     * @category Security-Critical
     */
    protected function isProtectedPage()
    {
        return true;
    }

    protected function handleAccessDenied($denyReason)
    {
        $currentUser = User::getCurrent($this->getDatabase());

        // Not allowed to access this resource.
        // Firstly, let's check if we're even logged in.
        if ($currentUser->isCommunityUser()) {
            // Not logged in, redirect to login page
            WebRequest::setPostLoginRedirect();
            $this->redirect("login");

            return;
        }
        else {
            // Decide whether this was a rights failure, or an identification failure.

            if ($denyReason === SecurityManager::ERROR_NOT_IDENTIFIED) {
                // Not identified
                throw new NotIdentifiedException($this->getSecurityManager());
            }
            elseif ($denyReason === SecurityManager::ERROR_DENIED) {
                // Nope, plain old access denied
                throw new AccessDeniedException($this->getSecurityManager());
            }
            else {
                throw new Exception('Unknown response from security manager.');
            }
        }
    }

    /**
     * Tests the security barrier for a specified action.
     *
     * Don't use within templates
     *
     * @param string      $action
     *
     * @param User        $user
     * @param null|string $pageName
     *
     * @return bool
     * @category Security-Critical
     */
    final public function barrierTest($action, User $user, $pageName = null)
    {
        $page = get_called_class();
        if ($pageName !== null) {
            $page = $pageName;
        }

        $securityResult = $this->getSecurityManager()->allows($page, $action, $user);

        return $securityResult === SecurityManager::ALLOWED;
    }

    /**
     * Updates the lastactive timestamp
     */
    private function touchUserLastActive()
    {
        if (WebRequest::getSessionUserId() !== null) {
            $query = 'UPDATE user SET lastactive = CURRENT_TIMESTAMP() WHERE id = :id;';
            $this->getDatabase()->prepare($query)->execute(array(":id" => WebRequest::getSessionUserId()));
        }
    }

    /**
     * @return SecurityManager
     */
    public function getSecurityManager()
    {
        return $this->securityManager;
    }

    /**
     * @param SecurityManager $securityManager
     */
    public function setSecurityManager(SecurityManager $securityManager)
    {
        $this->securityManager = $securityManager;
    }

    /**
     * @return IBlacklistHelper
     */
    public function getBlacklistHelper()
    {
        return $this->blacklistHelper;
    }

    /**
     * @param IBlacklistHelper $blacklistHelper
     */
    public function setBlacklistHelper(IBlacklistHelper $blacklistHelper)
    {
        $this->blacklistHelper = $blacklistHelper;
    }
}
