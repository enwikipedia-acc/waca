<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Helpers;

use Waca\Helpers\Interfaces\IBlacklistHelper;

class FakeBlacklistHelper implements IBlacklistHelper
{
    /**
     * Returns a value indicating whether the provided username is blacklisted by the on-wiki title blacklist
     *
     * @param string $username
     *
     * @return bool
     */
    public function isBlacklisted($username)
    {
        // Short-circuit
        return false;
    }
}