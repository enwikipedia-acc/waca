<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\UserAuth\Login;

use Waca\Exceptions\ApplicationLogicException;
use Waca\WebRequest;

class PageOtpLogin extends LoginCredentialPageBase
{
    protected function providerSpecificSetup()
    {
        $this->setTemplate('login/otp.tpl');
    }

    protected function getProviderCredentials()
    {
        $otp = WebRequest::postString("otp");
        if ($otp === null || $otp === "") {
            throw new ApplicationLogicException("No one-time code specified");
        }

        return $otp;
    }
}