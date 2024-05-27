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
use Waca\Fragments\NavigationMenuAccessControl;
use Waca\Helpers\PreferenceManager;
use Waca\PdoDatabase;
use Waca\Security\IDomainAccessManager;
use Waca\Security\ISecurityManager;

class NotIdentifiedException extends ReadableException
{
    use NavigationMenuAccessControl;

    private ISecurityManager $securityManager;
    private IDomainAccessManager $domainAccessManager;

    public function __construct(ISecurityManager $securityManager, IDomainAccessManager $domainAccessManager)
    {
        $this->securityManager = $securityManager;
        $this->domainAccessManager = $domainAccessManager;
    }

    /**
     * Returns a readable HTML error message that's displayable to the user using templates.
     * @return string
     */
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

        return $this->fetchTemplate("exception/not-identified.tpl");
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