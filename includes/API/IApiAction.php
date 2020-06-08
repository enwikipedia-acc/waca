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
interface IApiAction extends IRoutedTask
{
    /**
     * @return string the XML, or false if an error occurred.
     */
    public function runApiPage();
}
