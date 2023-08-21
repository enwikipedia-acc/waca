<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Fragments;

use Waca\DataObjects\Comment;
use Waca\DataObjects\JobQueue;
use Waca\DataObjects\User;
use Waca\Helpers\SearchHelpers\JobQueueSearchHelper;
use Waca\Pages\PageBan;
use Waca\Pages\PageDomainManagement;
use Waca\Pages\PageEmailManagement;
use Waca\Pages\PageErrorLogViewer;
use Waca\Pages\PageJobQueue;
use Waca\Pages\PageListFlaggedComments;
use Waca\Pages\PageLog;
use Waca\Pages\PageMain;
use Waca\Pages\PageQueueManagement;
use Waca\Pages\PageRequestFormManagement;
use Waca\Pages\PageSearch;
use Waca\Pages\PageSiteNotice;
use Waca\Pages\PageUserManagement;
use Waca\Pages\PageViewRequest;
use Waca\Pages\PageWelcomeTemplateManagement;
use Waca\Pages\Statistics\StatsMain;
use Waca\Pages\Statistics\StatsUsers;
use Waca\PdoDatabase;
use Waca\Security\DomainAccessManager;
use Waca\Security\RoleConfiguration;
use Waca\Security\SecurityManager;

trait NavigationMenuAccessControl
{
    protected abstract function assign($name, $value);

    /**
     * @return SecurityManager
     */
    protected abstract function getSecurityManager();

    public abstract function getDomainAccessManager(): DomainAccessManager;

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
        $this->assign('nav__canDomainMgmt', $this->getSecurityManager()
                ->allows(PageDomainManagement::class, RoleConfiguration::MAIN,
                    $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canFlaggedComments', $this->getSecurityManager()
                ->allows(PageListFlaggedComments::class, RoleConfiguration::MAIN,
                    $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canQueueMgmt', $this->getSecurityManager()
                ->allows(PageQueueManagement::class, RoleConfiguration::MAIN,
                    $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canFormMgmt', $this->getSecurityManager()
                ->allows(PageRequestFormManagement::class, RoleConfiguration::MAIN,
                    $currentUser) === SecurityManager::ALLOWED);
        $this->assign('nav__canErrorLog', $this->getSecurityManager()
                ->allows(PageErrorLogViewer::class, RoleConfiguration::MAIN, $currentUser) === SecurityManager::ALLOWED);

        $this->assign('nav__canViewRequest', $this->getSecurityManager()
                ->allows(PageViewRequest::class, RoleConfiguration::MAIN, $currentUser) === SecurityManager::ALLOWED);

        $this->assign('nav__domainList', []);
        if ($this->getDomainAccessManager() !== null) {
            $this->assign('nav__domainList', $this->getDomainAccessManager()->getAllowedDomains($currentUser));
        }
    }

    /**
     * Sets up the badges to draw attention to issues on various admin pages.
     *
     * This function checks to see if a user can access the pages, and if so checks the count of problem areas.
     * If problem areas are found, a number greater than 0 will cause the badge to show up.
     *
     * @param User        $currentUser The current user
     * @param PdoDatabase $database    Database instance
     *
     * @return void
     */
    public function setUpNavBarBadges(User $currentUser, PdoDatabase $database) {
        // Set up some variables.
        // A size of 0 causes nothing to show up on the page (checked on navigation-menu.tpl) so leaving it 0 here is fine.
        $countOfFlagged = 0;
        $countOfJobQueue = 0;

        // Count of flagged comments:
        if($this->barrierTest(RoleConfiguration::MAIN, $currentUser, PageListFlaggedComments::class)) {
            // We want all flagged comments that haven't been acknowledged if we can visit the page.
            $countOfFlagged = sizeof(Comment::getFlaggedComments($database, 1)); // FIXME: domains
        }

        // Count of failed job queue changes:
        if($this->barrierTest(RoleConfiguration::MAIN, $currentUser, PageJobQueue::class)) {
            // We want all failed jobs that haven't been acknowledged if we can visit the page.
            JobQueueSearchHelper::get($database, 1) // FIXME: domains
                ->statusIn([JobQueue::STATUS_FAILED])
                ->notAcknowledged()
                ->getRecordCount($countOfJobQueue);
        }

        // To generate the main badge, add both up.
        // If we add more badges in the future, don't forget to add them here!
        $countOfAll = $countOfFlagged + $countOfJobQueue;

        // Set badge variables
        $this->assign("nav__numFlaggedComments", $countOfFlagged);
        $this->assign("nav__numJobQueueFailed", $countOfJobQueue);
        $this->assign("nav__numAdmin", $countOfAll);
    }
}
