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

    /**
     * Not a very smart way of doing this - just set the user's current domain to the first one in the list.
     *
     * We may wish to allow the user to configure a default domain, but I don't expect this to be needed by many people,
     * so for now they can suffer until someone complains.
     *
     * @param User $user
     *
     * @return void
     */
    public function switchToDefaultDomain(User $user): void
    {
        $domains = $this->getAllowedDomains($user);
        if (count($domains) > 0) {
            WebRequest::setActiveDomain($domains[0]);
        }
    }
}