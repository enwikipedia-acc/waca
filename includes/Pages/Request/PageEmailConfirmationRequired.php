<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\Request;

use Waca\Tasks\PublicInterfacePageBase;

class PageEmailConfirmationRequired extends PublicInterfacePageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        // clear any requests for client hints
        $this->headerQueue[] = "Accept-CH:";

        $this->setTemplate('request/email-confirmation.tpl');
    }
}