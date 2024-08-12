<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Router;

use Waca\Pages\PagePublicPrivacy;
use Waca\Pages\Request\PageConfirmEmail;
use Waca\Pages\Request\PageEmailConfirmationRequired;
use Waca\Pages\Request\PageRequestAccount;
use Waca\Pages\Request\PageRequestSubmitted;

class PublicRequestRouter extends RequestRouter
{
    /**
     * Gets the route map to be used by this request router.
     *
     * @return array
     */
    protected function getRouteMap()
    {
        return array(
            // Page showing a message stating the request has been submitted to our internal queues
            'requestSubmitted'          =>
                array(
                    'class'   => PageRequestSubmitted::class,
                    'actions' => array(),
                ),
            // Page showing a message stating that email confirmation is required to continue
            'emailConfirmationRequired' =>
                array(
                    'class'   => PageEmailConfirmationRequired::class,
                    'actions' => array(),
                ),
            // Action page which handles email confirmation
            'confirmEmail'              =>
                array(
                    'class'   => PageConfirmEmail::class,
                    'actions' => array(),
                ),
            // Page showing the privacy statement
            'privacy'                   =>
                array(
                    'class'   => PagePublicPrivacy::class,
                    'actions' => array(),
                ),
        );
    }

    /**
     * Gets the default route if no explicit route is requested.
     *
     * @return callable
     */
    protected function getDefaultRoute()
    {
        return array(PageRequestAccount::class, 'main');
    }

    public function getRouteFromPath($pathInfo): array
    {
        if (count($pathInfo) === 3 && $pathInfo[0] === 'r') {
            // this request should be routed to the dynamic request form handler
            return [PageRequestAccount::class, 'dynamic'];
        }
        else {
            return parent::getRouteFromPath($pathInfo);
        }
    }
}