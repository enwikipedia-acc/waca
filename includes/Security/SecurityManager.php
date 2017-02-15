<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;
use Waca\IdentificationVerifier;

final class SecurityManager
{
    /** @var IdentificationVerifier */
    private $identificationVerifier;
    /** @var SecurityConfigurationFactory */
    private $securityConfigurationFactory;

    /**
     * SecurityManager constructor.
     *
     * @param IdentificationVerifier $identificationVerifier
     * @param bool                   $forceIdentification
     */
    public function __construct(IdentificationVerifier $identificationVerifier, $forceIdentification)
    {
        $this->identificationVerifier = $identificationVerifier;

        $this->securityConfigurationFactory = new SecurityConfigurationFactory($forceIdentification);
    }

    public function configure()
    {
        return $this->securityConfigurationFactory;
    }

    /**
     * @param $value
     * @param $filter
     *
     * @return bool
     * @throws AccessDeniedException
     * @category Security-Critical
     */
    private function test($value, $filter)
    {
        if (!$filter) {
            return false;
        }

        if ($value == SecurityConfiguration::DENY) {
            // FILE_NOT_FOUND...?
            throw new AccessDeniedException();
        }

        return $value === SecurityConfiguration::ALLOW;
    }

    /**
     * Tests if a user is allowed to perform an action.
     *
     * This method should form a hard, deterministic security barrier, and only return true if it is absolutely sure
     * that a user should have access to something.
     *
     * @param SecurityConfiguration $config
     * @param User                  $user
     *
     * @return bool
     *
     * @category Security-Critical
     */
    public function allows(SecurityConfiguration $config, User $user)
    {
        if ($config->requiresIdentifiedUser() && !$user->isCommunityUser() && !$user->isIdentified($this->identificationVerifier)) {
            return false;
        }

        try {
            $allowed = $this->test($config->getAdmin(), $user->isAdmin())
                || $this->test($config->getUser(), $user->isUser())
                || $this->test($config->getCommunity(), $user->isCommunityUser())
                || $this->test($config->getSuspended(), $user->isSuspended())
                || $this->test($config->getDeclined(), $user->isDeclined())
                || $this->test($config->getNew(), $user->isNewUser())
                || $this->test($config->getCheckuser(), $user->isCheckuser());

            return $allowed;
        }
        catch (AccessDeniedException $ex) {
            // something is set to deny.
            return false;
        }
    }
}