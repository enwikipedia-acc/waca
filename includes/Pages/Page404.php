<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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

        $this->setTemplate("404.tpl");
    }

    protected function isProtectedPage()
    {
        return false;
    }
}
