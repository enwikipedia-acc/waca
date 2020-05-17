<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\UserAuth\Login;

use Waca\Exceptions\ApplicationLogicException;
use Waca\Security\CredentialProviders\U2FCredentialProvider;
use Waca\WebRequest;

class PageU2FLogin extends LoginCredentialPageBase
{
    protected function providerSpecificSetup()
    {
        $this->assign('showSignIn', false);
        $this->setTemplate('login/u2f.tpl');

        if ($this->partialUser === null) {
            throw new ApplicationLogicException("U2F cannot be first-stage authentication");
        }

        $u2f = new U2FCredentialProvider($this->getDatabase(), $this->getSiteConfiguration());
        $authData = json_encode($u2f->getAuthenticationData($this->partialUser));

        $this->addJs('/vendor/yubico/u2flib-server/examples/assets/u2f-api.js');
        $this->setTailScript(<<<JS
var request = ${authData};
console.log("starting sign");
u2f.sign(request, function(data) {
                var form = document.getElementById('loginCredentialForm');
                var reg = document.getElementById('authenticate');
                var req = document.getElementById('request');
                
                reg.value=JSON.stringify(data);
                req.value=JSON.stringify(request);
                console.log("signed, submitting");
                console.log(data);
                
                console.log(form);
                form.submit();
            });
JS
        );

    }

    protected function getProviderCredentials()
    {
        $authenticate = WebRequest::postString("authenticate");
        $request = WebRequest::postString("request");

        if ($authenticate === null || $authenticate === "" || $request === null || $request === "") {
              throw new ApplicationLogicException("No authentication specified");
        }

        return array(json_decode($authenticate), json_decode($request), 'u2f');
    }
}