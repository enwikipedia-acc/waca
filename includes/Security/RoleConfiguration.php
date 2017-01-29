<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

use Waca\Pages\PageBan;
use Waca\Pages\PageEditComment;
use Waca\Pages\PageEmailManagement;
use Waca\Pages\PageExpandedRequestList;
use Waca\Pages\PageLog;
use Waca\Pages\PageMain;
use Waca\Pages\PageOAuth;
use Waca\Pages\PagePreferences;
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

class RoleConfiguration
{
    const ACCESS_ALLOW = 1;
    const ACCESS_DENY = -1;
    const ACCESS_DEFAULT = 0;
    const MAIN = 'main';
    const ALL = '*';
    /**
     * A map of roles to rights
     *
     * For example:
     *
     * array(
     *   'myrole' => array(
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
     */
    private $roleConfig = array(
        'public'            => array(
            /*
             * THIS ROLE IS GRANTED TO ALL LOGGED *OUT* USERS IMPLICITLY.
             *
             * USERS IN THIS ROLE DO NOT HAVE TO BE IDENTIFIED TO GET THE RIGHTS CONFERRED HERE.
             * DO NOT ADD ANY SECURITY-SENSITIVE RIGHTS HERE.
             */
            '_childRoles'    => array(
                'publicStats',
            ),
            PageOAuth::class => array(
                'callback' => self::ACCESS_ALLOW,
            ),
            PageTeam::class  => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
        ),
        'loggedIn'            => array(
            /*
             * THIS ROLE IS GRANTED TO ALL LOGGED IN USERS IMPLICITLY.
             *
             * USERS IN THIS ROLE DO NOT HAVE TO BE IDENTIFIED TO GET THE RIGHTS CONFERRED HERE.
             * DO NOT ADD ANY SECURITY-SENSITIVE RIGHTS HERE.
             */
            '_childRoles'    => array(
                'public',
            ),
            PagePreferences::class               => array(
                self::MAIN       => self::ACCESS_ALLOW,
                'changePassword' => self::ACCESS_ALLOW,
            ),
            PageOAuth::class                     => array(
                'attach' => self::ACCESS_ALLOW,
                'detach' => self::ACCESS_ALLOW,
            ),
        ),
        'user'              => array(
            '_description' => 'A standard tool user.',
            '_editableBy' => array('admin', 'toolRoot'),
            '_childRoles'                        => array(
                'internalStats',
            ),
            PageMain::class                      => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageBan::class                       => array(
                self::MAIN => self::ACCESS_ALLOW,
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
            ),
            PageWelcomeTemplateManagement::class => array(
                self::MAIN => self::ACCESS_ALLOW,
                'select'   => self::ACCESS_ALLOW,
                'view'     => self::ACCESS_ALLOW,
            ),
            PageViewRequest::class               => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            'RequestData'                        => array(
                'seePrivateDataWhenReserved' => self::ACCESS_ALLOW,
                'seePrivateDataWithHash'     => self::ACCESS_ALLOW,
            ),
            PageCustomClose::class               => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageComment::class                   => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageCloseRequest::class              => array(
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

        ),
        'admin'             => array(
            '_description' => 'A tool administrator.',
            '_editableBy' => array('admin', 'toolRoot'),
            '_childRoles'                        => array(
                'user', 'requestAdminTools',
            ),
            PageEmailManagement::class           => array(
                'edit'   => self::ACCESS_ALLOW,
                'create' => self::ACCESS_ALLOW,
            ),
            PageSiteNotice::class                => array(
                self::MAIN => self::ACCESS_ALLOW,
            ),
            PageUserManagement::class            => array(
                self::MAIN  => self::ACCESS_ALLOW,
                'approve'   => self::ACCESS_ALLOW,
                'decline'   => self::ACCESS_ALLOW,
                'rename'    => self::ACCESS_ALLOW,
                'editUser'  => self::ACCESS_ALLOW,
                'suspend'   => self::ACCESS_ALLOW,
                'editRoles' => self::ACCESS_ALLOW,
            ),
            PageWelcomeTemplateManagement::class => array(
                'edit'   => self::ACCESS_ALLOW,
                'delete' => self::ACCESS_ALLOW,
                'add'    => self::ACCESS_ALLOW,
            ),
        ),
        'checkuser'         => array(
            '_description' => 'A user with CheckUser access',
            '_editableBy' => array('checkuser', 'toolRoot'),
            '_childRoles'             => array(
                'user', 'requestAdminTools',
            ),
            PageUserManagement::class => array(
                self::MAIN  => self::ACCESS_ALLOW,
                'suspend'   => self::ACCESS_ALLOW,
                'editRoles' => self::ACCESS_ALLOW,
            ),
            'RequestData'             => array(
                'seeUserAgentData' => self::ACCESS_ALLOW,
            ),
        ),
        'toolRoot'         => array(
            '_description' => 'A user with shell access to the servers running the tool',
            '_editableBy' => array('toolRoot'),
            '_childRoles'             => array(
                'admin', 'checkuser',
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
            StatsMonthlyStats::class     => array(
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
    private $identificationExempt = array('public', 'loggedIn');

    /**
     * RoleConfiguration constructor.
     *
     * @param array $roleConfig           Set to non-null to override the default configuration.
     * @param array $identificationExempt Set to non-null to override the default configuration.
     */
    public function __construct(array $roleConfig = null, array $identificationExempt = null)
    {
        if ($roleConfig !== null) {
            $this->roleConfig = $roleConfig;
        }

        if ($identificationExempt !== null) {
            $this->identificationExempt = $identificationExempt;
        }
    }

    /**
     * @param array $roles The roles to check
     *
     * @return array
     */
    public function getApplicableRoles(array $roles)
    {
        $available = array();

        foreach ($roles as $role) {
            if (!isset($this->roleConfig[$role])) {
                // wat
                continue;
            }

            $available[$role] = $this->roleConfig[$role];

            if (isset($available[$role]['_childRoles'])) {
                $childRoles = self::getApplicableRoles($available[$role]['_childRoles']);
                $available = array_merge($available, $childRoles);

                unset($available[$role]['_childRoles']);
            }

            foreach (array('_hidden', '_editableBy', '_description') as $item) {
                if (isset($available[$role][$item])) {
                    unset($available[$role][$item]);
                }
            }
        }

        return $available;
    }

    public function getAvailableRoles()
    {
        $possible = array_diff(array_keys($this->roleConfig), array('public', 'loggedIn'));

        $actual = array();

        foreach ($possible as $role) {
            if (!isset($this->roleConfig[$role]['_hidden'])) {
                $actual[$role] = array(
                    'description' => $this->roleConfig[$role]['_description'],
                    'editableBy'  => $this->roleConfig[$role]['_editableBy'],
                );
            }
        }

        return $actual;
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public function roleNeedsIdentification($role)
    {
        if (in_array($role, $this->identificationExempt)) {
            return false;
        }

        return true;
    }
}
