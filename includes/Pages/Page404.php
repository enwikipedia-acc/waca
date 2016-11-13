<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\Security\SecurityConfiguration;
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

    /**
     * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
     * the return value from this function.
     *
     * If this page even supports actions, you will need to check the route
     *
     * @return SecurityConfiguration
     * @category Security-Critical
     */
    protected function getSecurityConfiguration()
    {
        // public because 404s will never contain private data.
        return $this->getSecurityManager()->configure()->asPublicPage();
    }
}