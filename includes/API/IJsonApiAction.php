<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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
