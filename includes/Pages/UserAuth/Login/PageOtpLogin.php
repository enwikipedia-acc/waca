<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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