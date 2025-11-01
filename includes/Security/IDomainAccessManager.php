<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

use Waca\DataObjects\Domain;
use Waca\DataObjects\User;

interface IDomainAccessManager
{
    public function switchToDefaultDomain(User $user): void;

    public function switchDomain(User $user, Domain $newDomain): void;

    /**
     * @param User $user
     *
     * @return Domain[]
     */
    public function getAllowedDomains(User $user): array;
}