<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

use Waca\Exceptions\EnvironmentException;

/**
 * Handles automatically verifying if users are identified with the Wikimedia Foundation or not.  Intended to be used
 * as necessary by the User class when a user's "forceidentified" attribute is NULL.
 *
 * @category Security-Critical
 */
interface IIdentificationVerifier
{
    /**
     * Checks if the given user is identified to the Wikimedia Foundation.
     *
     * @param string $onWikiName The Wikipedia username of the user
     *
     * @return bool
     * @throws EnvironmentException
     * @category Security-Critical
     */
    public function isUserIdentified(string $onWikiName): bool;
}