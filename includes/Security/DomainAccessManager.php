<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

use Waca\DataObject;
use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\WebRequest;

class DomainAccessManager
{
    /**
     * @var SecurityManager
     */
    private $securityManager;

    public function __construct(SecurityManager $securityManager)
    {
        $this->securityManager = $securityManager;
    }

    /**
     * @param User $user
     *
     * @return Domain[]
     */
    public function getAllowedDomains(User $user): array
    {
        if ($user->isCommunityUser()) {
            return [];
        }

        return Domain::getDomainByUser($user->getDatabase(), $user, true);
    }

    public function switchDomain(User $user, Domain $newDomain): void
    {
        $mapToId = function(DataObject $object) {
            return $object->getId();
        };

        $allowed = in_array($newDomain->getId(), array_map($mapToId, self::getAllowedDomains($user)));

        if ($allowed) {
            WebRequest::setActiveDomain($newDomain);
        }
        else {
            throw new AccessDeniedException($this->securityManager, $this);
        }
    }
}