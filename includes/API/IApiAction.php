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
interface IApiAction extends IRoutedTask
{
    /**
     * @return string the XML, or false if an error occurred.
     */
    public function runApiPage();
}
