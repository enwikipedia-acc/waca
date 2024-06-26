<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tasks;

use Exception;

interface IRoutedTask extends ITask
{
    /**
     * Sets the route the request will take. Only should be called from the request router.
     *
     * @param $routeName string
     *
     * @return void
     *
     * @throws Exception
     * @category Security-Critical
     */
    public function setRoute($routeName);

    /**
     * Gets the name of the route that has been passed from the request router.
     * @return string
     */
    public function getRouteName();
}