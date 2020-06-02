<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security\CredentialProviders\WebAuthn;

use Waca\DataObjects\User;
use Webauthn\PublicKeyCredentialUserEntity as WebAuthnPublicKeyCredentialUserEntity;

class PublicKeyCredentialUserEntity extends WebAuthnPublicKeyCredentialUserEntity
{
    public function __construct(User $user, ?string $icon = null)
    {
        parent::__construct($user->getUsername(),
            $user->getId(),
            $user->getUsername(), $icon);
    }
}