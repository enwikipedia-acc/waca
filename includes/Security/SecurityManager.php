<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

use Waca\DataObjects\User;
use Waca\DataObjects\UserRole;
use Waca\IdentificationVerifier;

final class SecurityManager
{
    const ALLOWED = 1;
    const ERROR_NOT_IDENTIFIED = 2;
    const ERROR_DENIED = 3;
    /** @var IdentificationVerifier */
    private $identificationVerifier;
    /**
     * @var RoleConfiguration
     */
    private $roleConfiguration;

    /**
     * SecurityManager constructor.
     *
     * @param IdentificationVerifier $identificationVerifier
     * @param RoleConfiguration      $roleConfiguration
     */
    public function __construct(
        IdentificationVerifier $identificationVerifier,
        RoleConfiguration $roleConfiguration
    ) {
        $this->identificationVerifier = $identificationVerifier;
        $this->roleConfiguration = $roleConfiguration;
    }

    /**
     * Tests if a user is allowed to perform an action.
     *
     * This method should form a hard, deterministic security barrier, and only return true if it is absolutely sure
     * that a user should have access to something.
     *
     * @param string $page
     * @param string $route
     * @param User   $user
     *
     * @return int
     *
     * @category Security-Critical
     */
    public function allows($page, $route, User $user)
    {
        $this->getActiveRoles($user, $activeRoles, $inactiveRoles);

        $availableRights = $this->flattenRoles($activeRoles);
        $testResult = $this->findResult($availableRights, $page, $route);

        if ($testResult !== null) {
            // We got a firm result here, so just return it.
            return $testResult;
        }

        // No firm result yet, so continue testing the inactive roles so we can give a better error.
        $inactiveRights = $this->flattenRoles($inactiveRoles);
        $testResult = $this->findResult($inactiveRights, $page, $route);

        if ($testResult === self::ALLOWED) {
            // The user is allowed to access this, but their role is inactive.
            return self::ERROR_NOT_IDENTIFIED;
        }

        // Other options from the secondary test are denied and inconclusive, which at this point defaults to denied.
        return self::ERROR_DENIED;
    }

    /**
     * @param array  $pseudoRole The role (flattened) to check
     * @param string $page       The page class to check
     * @param string $route      The page route to check
     *
     * @return int|null
     */
    private function findResult($pseudoRole, $page, $route)
    {
        if (isset($pseudoRole[$page])) {
            // check for deny on catch-all route
            if (isset($pseudoRole[$page][RoleConfiguration::ALL])) {
                if ($pseudoRole[$page][RoleConfiguration::ALL] === RoleConfiguration::ACCESS_DENY) {
                    return self::ERROR_DENIED;
                }
            }

            // check normal route
            if (isset($pseudoRole[$page][$route])) {
                if ($pseudoRole[$page][$route] === RoleConfiguration::ACCESS_DENY) {
                    return self::ERROR_DENIED;
                }

                if ($pseudoRole[$page][$route] === RoleConfiguration::ACCESS_ALLOW) {
                    return self::ALLOWED;
                }
            }

            // check for allowed on catch-all route
            if (isset($pseudoRole[$page][RoleConfiguration::ALL])) {
                if ($pseudoRole[$page][RoleConfiguration::ALL] === RoleConfiguration::ACCESS_ALLOW) {
                    return self::ALLOWED;
                }
            }
        }

        // return indeterminate result
        return null;
    }

    /**
     * Takes an array of roles and flattens the values to a single set.
     *
     * @param array $activeRoles
     *
     * @return array
     */
    private function flattenRoles($activeRoles)
    {
        $result = array();

        $roleConfig = $this->roleConfiguration->getApplicableRoles($activeRoles);

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
                        if ($result[$page][$action] === RoleConfiguration::ACCESS_DENY) {
                            continue;
                        }
                    }

                    if ($permission === RoleConfiguration::ACCESS_DEFAULT) {
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
     * @param User  $user
     * @param array $activeRoles
     * @param array $inactiveRoles
     */
    public function getActiveRoles(User $user, &$activeRoles, &$inactiveRoles)
    {
        // Default to the community user here, because the main user is logged out
        $identified = false;
        $userRoles = array('public');

        // if we're not the community user, get our real rights.
        if (!$user->isCommunityUser()) {
            // Check the user's status - only active users are allowed the effects of roles

            $userRoles[] = 'loggedIn';

            if ($user->isActive()) {
                $ur = UserRole::getForUser($user->getId(), $user->getDatabase());

                // NOTE: public is still in this array.
                foreach ($ur as $r) {
                    $userRoles[] = $r->getRole();
                }

                $identified = $user->isIdentified($this->identificationVerifier);
            }
        }

        $activeRoles = array();
        $inactiveRoles = array();

        foreach ($userRoles as $v) {
            if ($this->roleConfiguration->roleNeedsIdentification($v)) {
                if ($identified) {
                    $activeRoles[] = $v;
                }
                else {
                    $inactiveRoles[] = $v;
                }
            }
            else {
                $activeRoles[] = $v;
            }
        }
    }

    public function getRoleConfiguration()
    {
        return $this->roleConfiguration;
    }
}
