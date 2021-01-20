<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\UserAuth\Login;

use Waca\Exceptions\ApplicationLogicException;
use Waca\Security\CredentialProviders\WebAuthnCredentialProvider;
use Waca\WebRequest;

class PageWebAuthnLogin extends LoginCredentialPageBase
{
    protected function providerSpecificSetup()
    {
        $this->assign('showSignIn', false);
        $this->setTemplate('login/webauthn.tpl');

        $this->addJs('/resources/auth/webauthn-authenticate.js', 'module');

        if ($this->partialUser === null) {
            throw new ApplicationLogicException("WebAuthn cannot be first-stage authentication");
        }
    }

    protected function getProviderCredentials()
    {
        return WebRequest::postString("token");
    }

    /**
     * Entry point for the action JS call
     */
    protected function action()
    {
        $this->setupPartial();
        $rawData = file_get_contents("php://input");

        $credentialProvider = new WebAuthnCredentialProvider($this->getDatabase(), $this->getSiteConfiguration());

        // call cred provider
        $authenticationResult = $credentialProvider->completeAuthentication($rawData, $this->partialUser);

        // cred provider issues token
        // pass token to web UI, submit form
        $this->headerQueue[] = 'Content-Type: application/json';
        $this->assign('content', json_encode($authenticationResult));
        $this->setTemplate('raw.tpl');

        // form goes back to cred provider to validate token
        // login success?

        return;
    }

    /**
     * Entry point for the options JS call
     */
    protected function options()
    {
        $this->setupPartial();

        $credentialProvider = new WebAuthnCredentialProvider($this->getDatabase(), $this->getSiteConfiguration());

        $authenticationOptions = $credentialProvider->beginAuthentication($this->partialUser);

        $this->headerQueue[] = 'Content-Type: application/json';
        $this->assign('content', $authenticationOptions);
        $this->setTemplate('raw.tpl');

        return;
    }

}
