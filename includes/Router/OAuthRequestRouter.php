<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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