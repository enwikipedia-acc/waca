<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Router;

use Exception;
use Waca\Tasks\IRoutedTask;

/**
 * Interface IRequestRouter
 *
 * @package Waca\Router
 */
interface IRequestRouter
{
    /**
     * @return IRoutedTask
     * @throws Exception
     */
    public function route();
}