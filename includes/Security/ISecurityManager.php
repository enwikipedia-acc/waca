<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

use Waca\DataObjects\User;

interface ISecurityManager
{
    public const ALLOWED = 1;
    public const ERROR_NOT_IDENTIFIED = 2;
    public const ERROR_DENIED = 3;

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
    public function allows(string $page, string $route, User $user): int;

    public function getActiveRoles(User $user, ?array &$activeRoles, ?array &$inactiveRoles);

    public function getCachedActiveRoles(User $user, ?array &$activeRoles, ?array &$inactiveRoles): void;

    public function getAvailableRoles(): array;
}