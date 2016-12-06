<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers\Interfaces;

interface IBlacklistHelper
{
    /**
     * Returns a value indicating whether the provided username is blacklisted by the on-wiki title blacklist
     *
     * @param string $username
     *
     * @return bool
     */
    public function isBlacklisted($username);
}