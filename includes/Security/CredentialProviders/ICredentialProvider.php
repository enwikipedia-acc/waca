<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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

    /**
     * @param User $user
     */
    public function deleteCredential(User $user);

    /**
     * @param int $userId
     *
     * @return bool
     */
    public function userIsEnrolled($userId);
}