<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Exceptions;

use Waca\DataObjects\Domain;
use Waca\DataObjects\Log;
use Waca\DataObjects\User;
use Waca\Fragments\NavigationMenuAccessControl;
use Waca\Helpers\PreferenceManager;
use Waca\Helpers\SearchHelpers\LogSearchHelper;
use Waca\PdoDatabase;
use Waca\Security\DomainAccessManager;
use Waca\Security\SecurityManager;

/**
 * Class AccessDeniedException
 *
 * Thrown when a logged-in user does not have permissions to access a page
 *
 * @package Waca\Exceptions
 */
class AccessDeniedException extends ReadableException
{
    use NavigationMenuAccessControl;

    /** @var SecurityManager */
    private $securityManager;
    /** @var DomainAccessManager */
    private $domainAccessManager;

    /**
     * AccessDeniedException constructor.
     *
     * @param SecurityManager     $securityManager
     * @param DomainAccessManager $domainAccessManager
     */
    public function __construct(SecurityManager $securityManager, DomainAccessManager $domainAccessManager)
    {
        $this->securityManager = $securityManager;
        $this->domainAccessManager = $domainAccessManager;
    }

    public function getReadableError()
    {
        if (!headers_sent()) {
            header("HTTP/1.1 403 Forbidden");
        }

        $this->setUpSmarty();

        // uck. We should still be able to access the database in this situation though.
        $database = PdoDatabase::getDatabaseConnection($this->getSiteConfiguration());
        $currentUser = User::getCurrent($database);
        $this->assign('skin', PreferenceManager::getForCurrent($database)->getPreference(PreferenceManager::PREF_SKIN));
        $this->assign('currentUser', $currentUser);
        $this->assign('currentDomain', Domain::getCurrent($database));

        if ($this->securityManager !== null) {
            $this->setupNavMenuAccess($currentUser);
        }

        if ($currentUser->isDeclined()) {
            $this->assign('htmlTitle', 'Account Declined');
            $this->assign('declineReason', $this->getLogEntry('Declined', $currentUser, $database));

            return $this->fetchTemplate("exception/account-declined.tpl");
        }

        if ($currentUser->isSuspended()) {
            $this->assign('htmlTitle', 'Account Suspended');
            $this->assign('suspendReason', $this->getLogEntry('Suspended', $currentUser, $database));

            return $this->fetchTemplate("exception/account-suspended.tpl");
        }

        if ($currentUser->isNewUser()) {
            $this->assign('htmlTitle', 'Account Pending');

            return $this->fetchTemplate("exception/account-new.tpl");
        }

        return $this->fetchTemplate("exception/access-denied.tpl");
    }

    /**
     * @param string      $action
     * @param User        $user
     * @param PdoDatabase $database
     *
     * @return null|string
     */
    private function getLogEntry($action, User $user, PdoDatabase $database)
    {
        /** @var Log[] $logs */
        $logs = LogSearchHelper::get($database, null)
            ->byAction($action)
            ->byObjectType('User')
            ->byObjectId($user->getId())
            ->limit(1)
            ->fetch();

        return $logs[0]->getComment();
    }

    protected function getSecurityManager(): SecurityManager
    {
        return $this->securityManager;
    }

    public function getDomainAccessManager(): DomainAccessManager
    {
        return $this->domainAccessManager;
    }
}