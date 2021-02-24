<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Fragments;

use Waca\Pages\PageBan;
use Waca\Pages\PageEmailManagement;
use Waca\Pages\PageErrorLogViewer;
use Waca\Pages\PageJobQueue;
use Waca\Pages\PageLog;
use Waca\Pages\PageMain;
use Waca\Pages\PageSearch;
use Waca\Pages\PageSiteNotice;
use Waca\Pages\PageUserManagement;
use Waca\Pages\PageViewRequest;
use Waca\Pages\PageWelcomeTemplateManagement;
use Waca\Pages\Statistics\StatsMain;
use Waca\Pages\Statistics\StatsUsers;
use Waca\Security\RoleConfiguration;
use Waca\Security\SecurityManager;

trait NavigationMenuAccessControl
{
    protected abstract function assign($name, $value);

    /**
     * @return SecurityManager
     */
    protected abstract function getSecurityManager();

    /**
     * @param $currentUser
     */
    protected function setupNavMenuAccess($currentUser)
    {
        $this->assign('nav__canRequests', $this->getSecurityManager()
                ->allows(PageMain::class, RoleConfiguration::MAIN, $currentUser) === SecurityManager::ALLOWED);

        $this->assign('nav__canLogs', $this->getSecurityManager()
                ->allows(PageLog::class, RoleConfiguration::MAIN, $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canUsers', $this->getSecurityManager()
                ->allows(StatsUsers::class, RoleConfiguration::MAIN, $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canSearch', $this->getSecurityManager()
                ->allows(PageSearch::class, RoleConfiguration::MAIN, $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canStats', $this->getSecurityManager()
                ->allows(StatsMain::class, RoleConfiguration::MAIN, $currentUser) === SecurityManager::ALLOWED);

        $this->assign('nav__canBan', $this->getSecurityManager()
                ->allows(PageBan::class, RoleConfiguration::MAIN, $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canEmailMgmt', $this->getSecurityManager()
                ->allows(PageEmailManagement::class, RoleConfiguration::MAIN,
                    $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canWelcomeMgmt', $this->getSecurityManager()
                ->allows(PageWelcomeTemplateManagement::class, RoleConfiguration::MAIN,
                    $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canSiteNoticeMgmt', $this->getSecurityManager()
                ->allows(PageSiteNotice::class, RoleConfiguration::MAIN, $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canUserMgmt', $this->getSecurityManager()
                ->allows(PageUserManagement::class, RoleConfiguration::MAIN,
                    $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canJobQueue', $this->getSecurityManager()
                ->allows(PageJobQueue::class, RoleConfiguration::MAIN,
                    $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canErrorLog', $this->getSecurityManager()
                ->allows(PageErrorLogViewer::class, RoleConfiguration::MAIN, $currentUser) === SecurityManager::ALLOWED);

        $this->assign('nav__canViewRequest', $this->getSecurityManager()
                ->allows(PageViewRequest::class, RoleConfiguration::MAIN, $currentUser) === SecurityManager::ALLOWED);
    }
}