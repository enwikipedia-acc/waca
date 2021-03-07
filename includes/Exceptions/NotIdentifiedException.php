<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Exceptions;

use Waca\DataObjects\User;
use Waca\Fragments\NavigationMenuAccessControl;
use Waca\PdoDatabase;
use Waca\Security\SecurityManager;

class NotIdentifiedException extends ReadableException
{
    use NavigationMenuAccessControl;
    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * NotIdentifiedException constructor.
     *
     * @param SecurityManager $securityManager
     */
    public function __construct(SecurityManager $securityManager = null)
    {
        $this->securityManager = $securityManager;
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

        if ($this->securityManager !== null) {
            $this->setupNavMenuAccess($currentUser);
        }

        return $this->fetchTemplate("exception/not-identified.tpl");
    }

    /**
     * @return SecurityManager
     */
    protected function getSecurityManager()
    {
        return $this->securityManager;
    }
}