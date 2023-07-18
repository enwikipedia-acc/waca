<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\Request;

use Waca\Tasks\PublicInterfacePageBase;

class PageRequestSubmitted extends PublicInterfacePageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     * @return void
     */
    protected function main()
    {
        // clear any requests for client hints. Should have been done already prior to
        // email confirmation, but this catches the case where email confirmation is
        // disabled.
        $this->headerQueue[] = "Accept-CH:";

        $this->setTemplate('request/email-confirmed.tpl');
    }
}