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
use Waca\DataObjects\User;
use Waca\Fragments\LogEntryLookup;
use Waca\Fragments\NavigationMenuAccessControl;
use Waca\Helpers\PreferenceManager;
use Waca\PdoDatabase;
use Waca\Security\IDomainAccessManager;
use Waca\Security\ISecurityManager;

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
    use LogEntryLookup;

    private ISecurityManager $securityManager;
    private IDomainAccessManager $domainAccessManager;

    /**
     * AccessDeniedException constructor.
     *
     * @param ISecurityManager     $securityManager
     * @param IDomainAccessManager $domainAccessManager
     */
    public function __construct(ISecurityManager $securityManager, IDomainAccessManager $domainAccessManager)
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

        $this->setupNavMenuAccess($currentUser);

        if ($currentUser->isDeactivated()) {
            $this->assign('htmlTitle', 'Account Deactivated');
            $this->assign('deactivationReason', $this->getLogEntry('DeactivatedUser', $currentUser, $database));

            return $this->fetchTemplate("exception/account-deactivated.tpl");
        }

        if ($currentUser->isNewUser()) {
            $this->assign('htmlTitle', 'Account Pending');

            return $this->fetchTemplate("exception/account-new.tpl");
        }

        return $this->fetchTemplate("exception/access-denied.tpl");
    }



    protected function getSecurityManager(): ISecurityManager
    {
        return $this->securityManager;
    }

    public function getDomainAccessManager(): IDomainAccessManager
    {
        return $this->domainAccessManager;
    }
}