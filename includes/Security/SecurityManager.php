<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Security;

use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\DataObjects\UserRole;
use Waca\IIdentificationVerifier;

final class SecurityManager implements ISecurityManager
{
    private IIdentificationVerifier $identificationVerifier;
    private RoleConfigurationBase $roleConfiguration;

    private array $cache = [];
    private IUserAccessLoader $userAccessLoader;

    public function __construct(
        IIdentificationVerifier $identificationVerifier,
        RoleConfigurationBase $roleConfiguration,
        IUserAccessLoader $userAccessLoader
    ) {
        $this->identificationVerifier = $identificationVerifier;
        $this->roleConfiguration = $roleConfiguration;
        $this->userAccessLoader = $userAccessLoader;
    }

    /**
     * Tests if a user is allowed to perform an action.
     *
     * This method should form a hard, deterministic security barrier, and only return true if it is absolutely sure
     * that a user should have access to something.
     *
     * @category Security-Critical
     */
    public function allows(string $page, string $route, User $user): int
    {
        $this->getCachedActiveRoles($user, $activeRoles, $inactiveRoles);

        $availableRights = $this->roleConfiguration->getResultantRole($activeRoles);
        $testResult = $this->findResult($availableRights, $page, $route);

        if ($testResult !== null) {
            // We got a firm result here, so just return it.
            return $testResult;
        }

        // No firm result yet, so continue testing the inactive roles so we can give a better error.
        $inactiveRights = $this->roleConfiguration->getResultantRole($inactiveRoles);
        $testResult = $this->findResult($inactiveRights, $page, $route);

        if ($testResult === self::ALLOWED) {
            // The user is allowed to access this, but their role is inactive.
            return self::ERROR_NOT_IDENTIFIED;
        }

        // Other options from the secondary test are denied and inconclusive, which at this point defaults to denied.
        return self::ERROR_DENIED;
    }

    public function getActiveRoles(User $user, ?array &$activeRoles, ?array &$inactiveRoles)
    {
        // Default to the community user here, because the main user is logged out
        $identified = false;
        $userRoles = array('public');

        // if we're not the community user, get our real rights.
        if (!$user->isCommunityUser()) {
            // Check the user's status - only active users are allowed the effects of roles

            $userRoles[] = 'loggedIn';

            if ($user->isActive()) {
                // All active users get +user
                $userRoles[] = 'user';

                $loadedRoles = $this->userAccessLoader->loadRolesForUser($user);

                // NOTE: public is still in this array.
                $userRoles = array_merge($userRoles, $loadedRoles);

                $identified = $this->userIsIdentified($user);
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

    public function getCachedActiveRoles(User $user, ?array &$activeRoles, ?array &$inactiveRoles): void
    {
        if (!array_key_exists($user->getId(), $this->cache)) {
            $this->getActiveRoles($user, $retrievedActiveRoles, $retrievedInactiveRoles);
            $this->cache[$user->getId()] = ['active' => $retrievedActiveRoles, 'inactive' => $retrievedInactiveRoles];
        }

        $activeRoles = $this->cache[$user->getId()]['active'];
        $inactiveRoles = $this->cache[$user->getId()]['inactive'];
    }

    public function getAvailableRoles(): array
    {
        return $this->roleConfiguration->getAvailableRoles();
    }

    /**
     * Tests a role for an ACL decision on a specific page/route
     *
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
            if (isset($pseudoRole[$page][RoleConfigurationBase::ALL])) {
                if ($pseudoRole[$page][RoleConfigurationBase::ALL] === RoleConfigurationBase::ACCESS_DENY) {
                    return self::ERROR_DENIED;
                }
            }

            // check normal route
            if (isset($pseudoRole[$page][$route])) {
                if ($pseudoRole[$page][$route] === RoleConfigurationBase::ACCESS_DENY) {
                    return self::ERROR_DENIED;
                }

                if ($pseudoRole[$page][$route] === RoleConfigurationBase::ACCESS_ALLOW) {
                    return self::ALLOWED;
                }
            }

            // check for allowed on catch-all route
            if (isset($pseudoRole[$page][RoleConfigurationBase::ALL])) {
                if ($pseudoRole[$page][RoleConfigurationBase::ALL] === RoleConfigurationBase::ACCESS_ALLOW) {
                    return self::ALLOWED;
                }
            }
        }

        // return indeterminate result
        return null;
    }

    private function userIsIdentified(User $user): bool
    {
        if ($user->getForceIdentified() === false) {
            // User forced to be unidentified in the database.
            return false;
        }

        if ($user->getForceIdentified() === true) {
            // User forced to be identified in the database.
            return true;
        }

        // User not forced to any particular identified status; consult IdentificationVerifier
        return $this->identificationVerifier->isUserIdentified($user->getOnWikiName());
    }
}
