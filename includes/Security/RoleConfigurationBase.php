<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

abstract class RoleConfigurationBase
{
    const ACCESS_ALLOW = 1;
    const ACCESS_DENY = -1;
    const ACCESS_DEFAULT = 0;
    const MAIN = 'main';
    const ALL = '*';

    protected array $roleConfig;
    protected array $identificationExempt;

    protected function __construct(array $roleConfig, array $identificationExempt)
    {
        $this->roleConfig = $roleConfig;
        $this->identificationExempt = $identificationExempt;
    }

    /**
     * Takes an array of role names and flattens the values to a single
     * resultant role configuration.
     *
     * @param string[] $activeRoles
     * @category Security-Critical
     */
    public function getResultantRole(array $activeRoles): array
    {
        $result = array();

        $roleConfig = $this->getApplicableRoles($activeRoles);

        // Iterate over every page in every role
        foreach ($roleConfig as $role) {
            foreach ($role as $page => $pageRights) {
                // Create holder in result for this page
                if (!isset($result[$page])) {
                    $result[$page] = array();
                }

                foreach ($pageRights as $action => $permission) {
                    // Deny takes precedence, so if it's set, don't change it.
                    if (isset($result[$page][$action])) {
                        if ($result[$page][$action] === RoleConfigurationBase::ACCESS_DENY) {
                            continue;
                        }
                    }

                    if ($permission === RoleConfigurationBase::ACCESS_DEFAULT) {
                        // Configured to do precisely nothing.
                        continue;
                    }

                    $result[$page][$action] = $permission;
                }
            }
        }

        return $result;
    }

    /**
     * Returns a set of all roles which are available to be set.
     *
     * Hidden roles and implicit roles are excluded.
     */
    public function getAvailableRoles(): array
    {
        // remove the implicit roles
        $possible = array_diff(array_keys($this->roleConfig), array('public', 'loggedIn', 'user'));

        $actual = array();

        foreach ($possible as $role) {
            if (!isset($this->roleConfig[$role]['_hidden'])) {
                $actual[$role] = array(
                    'description' => $this->roleConfig[$role]['_description'],
                    'editableBy'  => $this->roleConfig[$role]['_editableBy'],
                    'globalOnly'  => isset($this->roleConfig[$role]['_globalOnly']) && $this->roleConfig[$role]['_globalOnly'],
                );
            }
        }

        return $actual;
    }

    /**
     * Returns a boolean for whether the provided role requires identification
     * before being used by a user.
     *
     * @category Security-Critical
     */
    public function roleNeedsIdentification(string $role): bool
    {
        if (in_array($role, $this->identificationExempt)) {
            return false;
        }

        return true;
    }

    /**
     * Takes an array of role names, and returns all the relevant roles for that
     * set, including any child roles found recursively.
     *
     * @param array $roles The names of roles to start searching with
     */
    private function getApplicableRoles(array $roles): array
    {
        $available = array();

        foreach ($roles as $role) {
            if (!isset($this->roleConfig[$role])) {
                // wat
                continue;
            }

            $available[$role] = $this->roleConfig[$role];

            if (isset($available[$role]['_childRoles'])) {
                $childRoles = $this->getApplicableRoles($available[$role]['_childRoles']);
                $available = array_merge($available, $childRoles);

                unset($available[$role]['_childRoles']);
            }

            foreach (array('_hidden', '_editableBy', '_description', '_globalOnly') as $item) {
                if (isset($available[$role][$item])) {
                    unset($available[$role][$item]);
                }
            }
        }

        return $available;
    }
}