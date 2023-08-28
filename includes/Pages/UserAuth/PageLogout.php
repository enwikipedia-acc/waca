<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\UserAuth;

use Waca\Session;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PageLogout extends InternalPageBase
{
    /**
     * Main function for this page, when no specific actions are called.
     */
    protected function main()
    {
        if (WebRequest::wasPosted()) {
            Session::destroy();
            $this->redirect("login");
            return;
        }

        $this->redirect();
    }

    protected function isProtectedPage()
    {
        return false;
    }
}
