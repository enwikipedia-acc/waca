<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\Registration;

class PageRegisterStandard extends PageRegisterBase
{
    /**
     * @return string
     */
    protected function getRegistrationTemplate()
    {
        return "registration/register.tpl";
    }

    /**
     * @return string
     */
    protected function getDefaultRole()
    {
        return 'user';
    }
}
