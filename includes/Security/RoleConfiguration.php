<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

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
        'public'    => array(),
        'loggedIn'  => array(),
        'user'      => array(),
        'admin'     => array(),
        'checkuser' => array(),
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
            
            if (isset($available[$role]['_hidden'])) {
                unset($available[$role]['_hidden']);
            }
        }

        return $available;
    }

    public function getAvailableRoles()
    {
        return array_diff(array_keys($this->roleConfig), array('public', 'loggedIn'));
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
