<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\Tasks\InternalPageBase;

class Page404 extends InternalPageBase
{
    /**
     * Main function for this page, when no actions are called.
     */
    protected function main()
    {
        if (!headers_sent()) {
            header("HTTP/1.1 404 Not Found");
        }

        $this->skipAlerts();
        $this->setTemplate("404.tpl");
    }

    protected function isProtectedPage()
    {
        return false;
    }
}
