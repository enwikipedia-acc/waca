<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Exceptions;

use Waca\DataObjects\Log;
use Waca\DataObjects\User;
use Waca\Fragments\NavigationMenuAccessControl;
use Waca\Helpers\SearchHelpers\LogSearchHelper;
use Waca\PdoDatabase;
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

    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * AccessDeniedException constructor.
     *
     * @param SecurityManager $securityManager
     */
    public function __construct(SecurityManager $securityManager = null)
    {
        $this->securityManager = $securityManager;
    }

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
        $logs = LogSearchHelper::get($database)
            ->byAction($action)
            ->byObjectType('User')
            ->byObjectId($user->getId())
            ->limit(1)
            ->fetch();

        return $logs[0]->getComment();
    }

    /**
     * @return SecurityManager
     */
    protected function getSecurityManager()
    {
        return $this->securityManager;
    }
}