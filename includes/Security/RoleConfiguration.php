<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Security;

use Waca\Helpers\PreferenceManager;
use Waca\Pages\PageBan;
use Waca\Pages\PageDomainManagement;
use Waca\Pages\PageDomainSwitch;
use Waca\Pages\PageEditComment;
use Waca\Pages\PageEmailManagement;
use Waca\Pages\PageErrorLogViewer;
use Waca\Pages\PageExpandedRequestList;
use Waca\Pages\PageFlagComment;
use Waca\Pages\PageJobQueue;
use Waca\Pages\PageListFlaggedComments;
use Waca\Pages\PageLog;
use Waca\Pages\PageMain;
use Waca\Pages\PagePrivacy;
use Waca\Pages\PageQueueManagement;
use Waca\Pages\PageRequestFormManagement;
use Waca\Pages\PageXffDemo;
use Waca\Pages\RequestAction\PageCreateRequest;
use Waca\Pages\RequestAction\PageManuallyConfirm;
use Waca\Pages\UserAuth\PageChangePassword;
use Waca\Pages\UserAuth\MultiFactor\PageMultiFactor;
use Waca\Pages\UserAuth\PageOAuth;
use Waca\Pages\UserAuth\PagePreferences;
use Waca\Pages\PageSearch;
use Waca\Pages\PageSiteNotice;
use Waca\Pages\PageTeam;
use Waca\Pages\PageUserManagement;
use Waca\Pages\PageViewRequest;
use Waca\Pages\PageWelcomeTemplateManagement;
use Waca\Pages\RequestAction\PageBreakReservation;
use Waca\Pages\RequestAction\PageCloseRequest;
use Waca\Pages\RequestAction\PageComment;
use Waca\Pages\RequestAction\PageCustomClose;
use Waca\Pages\RequestAction\PageDeferRequest;
use Waca\Pages\RequestAction\PageDropRequest;
use Waca\Pages\RequestAction\PageReservation;
use Waca\Pages\RequestAction\PageSendToUser;
use Waca\Pages\Statistics\StatsFastCloses;
use Waca\Pages\Statistics\StatsInactiveUsers;
use Waca\Pages\Statistics\StatsMain;
use Waca\Pages\Statistics\StatsMonthlyStats;
use Waca\Pages\Statistics\StatsReservedRequests;
use Waca\Pages\Statistics\StatsTemplateStats;
use Waca\Pages\Statistics\StatsTopCreators;
use Waca\Pages\Statistics\StatsUsers;
use Waca\Pages\UserAuth\PageUserReactivate;

final class RoleConfiguration extends RoleConfigurationBase
{
    /**
     * A map of roles to rights
     *
     * For example:
     *
     * array(
     *   'myRole' => array(
     *       PageMyPage::class => array(
     *           'edit' => self::ACCESS_ALLOW,
     *           'create' => self::ACCESS_DENY,
     *       )
     *   )
     * )
     *
     * Note that DENY takes precedence over everything else when roles are combined, followed by ALLOW, followed by
     * DEFAULT. Thus, if you have the following ([A]llow, [D]eny, [-] (default)) grants in different roles, this should
     * be the expected result:
     *
     * - (-,-,-) = - (default because nothing to explicitly say allowed or denied equates to a denial)
     * - (A,-,-) = A
     * - (D,-,-) = D
     * - (A,D,-) = D (deny takes precedence over allow)
     * - (A,A,A) = A (repetition has no effect)
     *
     * The public role is special, and is applied to all users automatically. Avoid using deny on this role.
     *
     * @var array
     * @category Security-Critical
     */
    private static array $productionRoleConfig = array(
        'public'            => array(
            /*
             * THIS ROLE IS GRANTED TO ALL LOGGED *OUT* USERS IMPLICITLY.
             *
             * USERS IN THIS ROLE DO NOT HAVE TO BE IDENTIFIED TO GET THE RIGHTS CONFERRED HERE.
             * DO NOT ADD ANY SECURITY-SENSITIVE RIGHTS HERE.
             */
            '_childRoles'   => array(
                'publicStats',
            ),
            PageTeam::class => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageXffDemo::class        => array(
                self::MAIN  => self::ACCESS_ALLOW,
            ),
            PagePrivacy::class => array(
                self::MAIN => self::ACCESS_ALLOW,
            )
        ),
        'loggedIn'          => array(
            /*
             * THIS ROLE IS GRANTED TO ALL LOGGED-IN USERS IMPLICITLY.
             *
             * USERS IN THIS ROLE DO NOT HAVE TO BE IDENTIFIED TO GET THE RIGHTS CONFERRED HERE.
             * DO NOT ADD ANY SECURITY-SENSITIVE RIGHTS HERE.
             */
            '_childRoles'             => array(
                'public',
            ),
            PagePreferences::class    => array(
                self::MAIN => self::ACCESS_ALLOW,
                'refreshOAuth' => self::ACCESS_ALLOW,
            ),
            PageChangePassword::class => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageMultiFactor::class    => array(
                self::MAIN          => self::ACCESS_ALLOW,
                'scratch'           => self::ACCESS_ALLOW,
                'enableYubikeyOtp'  => self::ACCESS_ALLOW,
                'enableTotp'        => self::ACCESS_ALLOW,
                // allow a user to disable this even when they're not allowed to enable it
                'disableYubikeyOtp' => self::ACCESS_ALLOW,
                'disableTotp'       => self::ACCESS_ALLOW,
            ),
            PageOAuth::class          => array(
                'attach' => self::ACCESS_ALLOW,
                'detach' => self::ACCESS_ALLOW,
            ),
            PageDomainSwitch::class   => array(
                self::MAIN => self::ACCESS_ALLOW
            ),
            PageUserReactivate::class => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            'UserData'                => array(
                'accountLogSelf' => self::ACCESS_ALLOW,
            ),
        ),
        'user'              => array(
            /*
             * THIS ROLE IS GRANTED TO APPROVED AND IDENTIFIED LOGGED-IN USERS IMPLICITLY.
             */
            '_childRoles'                        => array(
                'internalStats',
            ),
            PageUserReactivate::class => array(
                // only non-approved users should be able to access this
                self::MAIN => self::ACCESS_DENY,
            ),
            PageMain::class                      => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageBan::class                       => array(
                self::MAIN => self::ACCESS_ALLOW,
                'show'     => self::ACCESS_ALLOW,
            ),
            'BanVisibility'             => array(
                'user' => self::ACCESS_ALLOW,
            ),
            'BanType'                   => array(
                'ip' => self::ACCESS_ALLOW,
                'name' => self::ACCESS_ALLOW,
            ),
            PageEditComment::class               => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageEmailManagement::class           => array(
                self::MAIN => self::ACCESS_ALLOW,
                'view'     => self::ACCESS_ALLOW,
            ),
            PageExpandedRequestList::class       => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageLog::class                       => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageSearch::class                    => array(
                self::MAIN => self::ACCESS_ALLOW,
                'byName'   => self::ACCESS_ALLOW,
                'byEmail'  => self::ACCESS_ALLOW,
                'byIp'     => self::ACCESS_ALLOW,
                'allowNonConfirmed' => self::ACCESS_ALLOW,
            ),
            PageWelcomeTemplateManagement::class => array(
                self::MAIN => self::ACCESS_ALLOW,
                'select'   => self::ACCESS_ALLOW,
                'view'     => self::ACCESS_ALLOW,
            ),
            PageViewRequest::class               => array(
                self::MAIN       => self::ACCESS_ALLOW,
                'seeAllRequests' => self::ACCESS_ALLOW,
            ),
            'RequestData'                        => array(
                'seePrivateDataWhenReserved' => self::ACCESS_ALLOW,
                'seePrivateDataWithHash'     => self::ACCESS_ALLOW,
                'seeRelatedRequests'         => self::ACCESS_ALLOW,
            ),
            PageCustomClose::class               => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageComment::class                   => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageFlagComment::class               => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageCloseRequest::class              => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageCreateRequest::class             => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageDeferRequest::class              => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageDropRequest::class               => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageReservation::class               => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageSendToUser::class                => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageBreakReservation::class          => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageJobQueue::class                  => array(
                self::MAIN    => self::ACCESS_ALLOW,
                'view'        => self::ACCESS_ALLOW,
                'all'         => self::ACCESS_ALLOW,
                'acknowledge' => self::ACCESS_ALLOW,
                'cancel'      => self::ACCESS_ALLOW
            ),
            PageDomainManagement::class          => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageRequestFormManagement::class     => array(
                self::MAIN => self::ACCESS_ALLOW,
                'view'     => self::ACCESS_ALLOW,
                'preview'  => self::ACCESS_ALLOW,
            ),
            'RequestCreation'                    => array(
                PreferenceManager::CREATION_MANUAL => self::ACCESS_ALLOW,
                PreferenceManager::CREATION_OAUTH  => self::ACCESS_ALLOW,
            ),
            'GlobalInfo'                         => array(
                'viewSiteNotice' => self::ACCESS_ALLOW,
                'viewOnlineUsers' => self::ACCESS_ALLOW,
            ),
        ),
        'admin'             => array(
            '_description'                       => 'A tool administrator.',
            '_editableBy'                        => array('admin', 'toolRoot'),
            '_childRoles'                        => array(
                'user',
                'requestAdminTools',
            ),
            PageEmailManagement::class           => array(
                'edit'   => self::ACCESS_ALLOW,
                'create' => self::ACCESS_ALLOW,
            ),
            PageSiteNotice::class                => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageUserManagement::class            => array(
                self::MAIN   => self::ACCESS_ALLOW,
                'approve'    => self::ACCESS_ALLOW,
                'deactivate' => self::ACCESS_ALLOW,
                'rename'     => self::ACCESS_ALLOW,
                'editUser'   => self::ACCESS_ALLOW,
                'editRoles'  => self::ACCESS_ALLOW,
            ),
            PageSearch::class                    => array(
                'byComment' => self::ACCESS_ALLOW,
            ),
            PageManuallyConfirm::class               => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageWelcomeTemplateManagement::class => array(
                'edit'   => self::ACCESS_ALLOW,
                'delete' => self::ACCESS_ALLOW,
                'add'    => self::ACCESS_ALLOW,
            ),
            PageJobQueue::class                  => array(
                'acknowledge' => self::ACCESS_ALLOW,
                'requeue'     => self::ACCESS_ALLOW,
                'cancel'      => self::ACCESS_ALLOW,
            ),
            'RequestData'               => array(
                'reopenClearedRequest'  => self::ACCESS_ALLOW,
            ),
            PageQueueManagement::class           => array(
                self::MAIN => self::ACCESS_ALLOW,
                'edit'     => self::ACCESS_ALLOW,
                'create'   => self::ACCESS_ALLOW,
            ),
            PageRequestFormManagement::class     => array(
                'edit'     => self::ACCESS_ALLOW,
                'create'   => self::ACCESS_ALLOW,
            ),
            PageDomainManagement::class          => array(
                'edit'     => self::ACCESS_ALLOW,
            ),
            'UserData'                           => array(
                'accountLog' => self::ACCESS_ALLOW,
            ),
        ),
        'checkuser'         => array(
            '_description'            => 'A user with CheckUser access',
            '_editableBy'             => array('checkuser', 'steward', 'toolRoot'),
            '_childRoles'             => array(
                'user',
                'requestAdminTools',
            ),
            PageUserManagement::class => array(
                self::MAIN   => self::ACCESS_ALLOW,
                'deactivate' => self::ACCESS_ALLOW,
                'editRoles'  => self::ACCESS_ALLOW,
            ),
            'RequestData'             => array(
                'seeUserAgentData'      => self::ACCESS_ALLOW,
                'seeCheckuserComments'  => self::ACCESS_ALLOW,
                'createLocalAccount'    => self::ACCESS_ALLOW,
            ),
            'BanType'                   => array(
                'useragent' => self::ACCESS_ALLOW,
            ),
            'BanVisibility'             => array(
                'checkuser' => self::ACCESS_ALLOW,
            ),
            'UserData'                           => array(
                'accountLog' => self::ACCESS_ALLOW,
            ),
        ),
        'steward'         => array(
            '_description'  => 'A user with Steward access',
            '_editableBy'   => array('steward', 'toolRoot'),
            '_globalOnly'   => true,
            '_childRoles'   => array(
                'user',
                'checkuser',
            ),
            'BanType'                   => array(
                'ip-largerange' => self::ACCESS_ALLOW,
                'global'        => self::ACCESS_ALLOW,
            ),
        ),
        'toolRoot'          => array(
            '_description' => 'A user with shell access to the servers running the tool',
            '_editableBy'  => array('toolRoot'),
            '_globalOnly'  => true,
            '_childRoles'  => array(
                'admin',
            ),
            'BanType'                   => array(
                'ip-largerange' => self::ACCESS_ALLOW,
                'global'        => self::ACCESS_ALLOW,
            ),
            PageDomainManagement::class => array(
                self::MAIN => self::ACCESS_ALLOW,
                'editAll'  => self::ACCESS_ALLOW,
                'edit'     => self::ACCESS_ALLOW,
                'create'   => self::ACCESS_ALLOW,
            ),
            PageErrorLogViewer::class => array(
                self::MAIN      => self::ACCESS_ALLOW,
                'view'          => self::ACCESS_ALLOW,
                'remove'        => self::ACCESS_ALLOW,
            ),
        ),
        'botCreation'       => array(
            '_hidden'         => true,
            '_description'    => 'A user allowed to use the bot to perform account creations',
            '_editableBy'     => array('admin', 'toolRoot'),
            '_childRoles'     => array(),
            'RequestCreation' => array(
                PreferenceManager::CREATION_BOT => self::ACCESS_ALLOW,
            ),
        ),

        // Child roles go below this point
        'publicStats'       => array(
            '_hidden'               => true,
            StatsUsers::class       => array(
                self::MAIN => self::ACCESS_ALLOW,
                'detail'   => self::ACCESS_ALLOW,
            ),
            StatsTopCreators::class => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            StatsMonthlyStats::class     => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
        ),
        'internalStats'     => array(
            '_hidden'                    => true,
            StatsMain::class             => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            StatsFastCloses::class       => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            StatsInactiveUsers::class    => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            StatsReservedRequests::class => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            StatsTemplateStats::class    => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
        ),
        'requestAdminTools' => array(
            '_hidden'                   => true,
            PageBan::class              => array(
                self::MAIN => self::ACCESS_ALLOW,
                'set'      => self::ACCESS_ALLOW,
                'remove'   => self::ACCESS_ALLOW,
                'replace'  => self::ACCESS_ALLOW,
            ),
            'BanType'                   => array(
                'ip' => self::ACCESS_ALLOW,
                'email' => self::ACCESS_ALLOW,
                'name' => self::ACCESS_ALLOW,
            ),
            'BanVisibility'             => array(
                'user' => self::ACCESS_ALLOW,
                'admin' => self::ACCESS_ALLOW,
            ),
            PageEditComment::class      => array(
                'editOthers' => self::ACCESS_ALLOW,
            ),
            PageBreakReservation::class => array(
                'force' => self::ACCESS_ALLOW,
            ),
            PageCustomClose::class      => array(
                'skipCcMailingList' => self::ACCESS_ALLOW,
            ),
            PageFlagComment::class      => array(
                'unflag'   => self::ACCESS_ALLOW,
            ),
            PageListFlaggedComments::class => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            'RequestData'               => array(
                'reopenOldRequest'      => self::ACCESS_ALLOW,
                'alwaysSeePrivateData'  => self::ACCESS_ALLOW,
                'alwaysSeeHash'         => self::ACCESS_ALLOW,
                'seeRestrictedComments' => self::ACCESS_ALLOW,
            ),
        ),
    );

    /** @var array
     * List of roles which are *exempt* from the identification requirements
     *
     * Think twice about adding roles to this list.
     *
     * @category Security-Critical
     */
    private static array $productionIdentificationExempt = array('public', 'loggedIn');

    public function __construct()
    {
        parent::__construct(self::$productionRoleConfig, self::$productionIdentificationExempt);
    }
}
