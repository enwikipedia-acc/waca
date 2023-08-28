<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\API;

use Waca\Tasks\IRoutedTask;

/**
 * API Action interface
 */
interface IJsonApiAction extends IRoutedTask, IApiAction
{
    /**
     * Method that runs API action
     *
     * @return object|array The modified API document
     */
    public function executeApiAction();
}
