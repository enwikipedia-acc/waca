<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\Registration;

use Waca\DataObjects\Domain;
use Waca\Tasks\InternalPageBase;

class PageRegisterOption extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        $this->assign('allowRegistration', $this->getSiteConfiguration()->isRegistrationAllowed());
        $this->assign('domains', Domain::getAll($this->getDatabase()));
        $this->setTemplate('registration/option.tpl');
    }

    protected function isProtectedPage()
    {
        return false;
    }
}
