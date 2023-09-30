<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

use Waca\Router\OAuthRequestRouter;

/*
 * OAuth callback script
 *
 * THIS IS AN ENTRY POINT
 */

require_once(__DIR__ . '/../includes/Startup.php');

global $siteConfiguration;
$application = new WebStart($siteConfiguration, new OAuthRequestRouter());

$application->run();
