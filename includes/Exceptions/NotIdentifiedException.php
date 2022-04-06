<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Exceptions;

use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\Fragments\NavigationMenuAccessControl;
use Waca\PdoDatabase;
use Waca\Security\DomainAccessManager;
use Waca\Security\SecurityManager;

class NotIdentifiedException extends ReadableException
{
    use NavigationMenuAccessControl;

    /** @var SecurityManager */
    private $securityManager;
    /** @var DomainAccessManager */
    private $domainAccessManager;

    /**
     * NotIdentifiedException constructor.
     *
     * @param SecurityManager     $securityManager
     * @param DomainAccessManager $domainAccessManager
     */
    public function __construct(SecurityManager $securityManager, DomainAccessManager $domainAccessManager)
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
        $database = PdoDatabase::getDatabaseConnection('acc');
        $currentUser = User::getCurrent($database);
        $this->assign('currentUser', $currentUser);
        $this->assign('currentDomain', Domain::getCurrent($database));

        if ($this->securityManager !== null) {
            $this->setupNavMenuAccess($currentUser);
        }

        return $this->fetchTemplate("exception/not-identified.tpl");
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