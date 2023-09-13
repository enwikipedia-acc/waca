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
use Waca\Helpers\PreferenceManager;
use Waca\WebRequest;

class DomainAccessManager
{
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

    public function switchDomain(User $user, Domain $newDomain, SecurityManager $securityManager): void
    {
        $mapToId = function(DataObject $object) {
            return $object->getId();
        };

        $allowed = in_array($newDomain->getId(), array_map($mapToId, self::getAllowedDomains($user)));

        if ($allowed) {
            WebRequest::setActiveDomain($newDomain);
        }
        else {
            throw new AccessDeniedException($securityManager, $this);
        }
    }

    public function switchToDefaultDomain(User $user): void
    {
        $domains = $this->getAllowedDomains($user);
        $preferenceManager = new PreferenceManager($user->getDatabase(), $user->getId(), null);
        $defaultDomainPreference = $preferenceManager->getPreference(PreferenceManager::PREF_DEFAULT_DOMAIN);

        $chosenDomain = null;
        foreach ($domains as $d) {
            if ($d->getId() == $defaultDomainPreference) {
                $chosenDomain = $d;
                break;
            }
        }

        if ($chosenDomain !== null) {
            WebRequest::setActiveDomain($chosenDomain);
            return;
        }

        if (count($domains) > 0) {
            WebRequest::setActiveDomain($domains[0]);
        }
    }
}