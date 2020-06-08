<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Router;

use Waca\Pages\UserAuth\PageOAuthCallback;

/**
 * Class OAuthRequestRouter
 *
 * @package Waca\Router
 */
class OAuthRequestRouter extends RequestRouter
{
    protected function getRouteFromPath($pathInfo)
    {
        // Hardcode the route for this entry point
        return array(PageOAuthCallback::class, 'authorise');
    }
}