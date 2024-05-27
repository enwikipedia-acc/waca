<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

use Waca\DataObjects\User;

interface IUserAccessLoader
{
    /**
     * Loads the roles for the given user in the current domain from the database.
     *
     * This is mostly just a wrapper around the static method calls so this logic
     * can be mocked out in unit tests.
     */
    public function loadRolesForUser(User $user): array;

    /**
     * Loads the domains which are currently active for the given user
     *
     * This is mostly just a wrapper around the static method calls so this logic
     * can be mocked out in unit tests.
     */
    public function loadDomainsForUser(User $user): array;
}