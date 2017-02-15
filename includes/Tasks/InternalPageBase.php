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
use Waca\Helpers\Interfaces\IBlacklistHelper;
use Waca\IdentificationVerifier;
use Waca\Helpers\Interfaces\ITypeAheadHelper;
use Waca\Security\SecurityConfiguration;
use Waca\Security\SecurityManager;
use Waca\WebRequest;

abstract class InternalPageBase extends PageBase
{
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
        if ($this->getSecurityManager()->allows($securityConfiguration, $currentUser)) {
            // We're allowed to run the page, so let's run it.
            $this->runPage();
        }
        else {
            $this->handleAccessDenied();

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

        if (!User::getCurrent($database)->isCommunityUser()) {
            $sql = 'SELECT * FROM user WHERE lastactive > DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL 5 MINUTE);';
            $statement = $database->query($sql);
            $activeUsers = $statement->fetchAll(PDO::FETCH_CLASS, User::class);
            $this->assign('onlineusers', $activeUsers);
        }
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

    protected function handleAccessDenied()
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

            if ($this->getSiteConfiguration()->getForceIdentification()
                && $currentUser->isIdentified($this->identificationVerifier) !== true
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
        $tmpRouteName = $this->getRouteName();

        try {
            $this->setRoute($action, true);

            $securityConfiguration = $this->getSecurityConfiguration();
            $currentUser = User::getCurrent($this->getDatabase());

            $allowed = $this->getSecurityManager()->allows($securityConfiguration, $currentUser);

            return $allowed;
        }
        finally {
            $this->setRoute($tmpRouteName);
        }
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