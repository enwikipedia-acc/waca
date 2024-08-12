<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca;

use Waca\Router\ApiRequestRouter;

/*
 * Public interface script
 *
 * THIS IS AN ENTRY POINT
 */

require_once('includes/Startup.php');

global $siteConfiguration;
$application = new WebStart($siteConfiguration, new ApiRequestRouter());

// This is a public interface
$application->setPublic(true);

$application->run();
