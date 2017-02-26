<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security\CredentialProviders;

use Waca\DataObjects\User;

interface ICredentialProvider
{
    /**
     * Validates a user-provided credential
     *
     * @param User $user The user to test the authentication against
     * @param string $data The raw credential data to be validated
     *
     * @return bool
     */
    public function authenticate(User $user, $data);

    /**
     * @param User $user The user the credential belongs to
     * @param int $factor The factor this credential provides
     * @param string $data
     */
    public function setCredential(User $user, $factor, $data);
}