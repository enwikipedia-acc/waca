<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\Session;
use Waca\Tasks\InternalPageBase;

class PageLogout extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     */
    protected function main()
    {
        Session::destroy();
        $this->redirect("login");
    }

    protected function isProtectedPage()
    {
        return false;
    }
}
