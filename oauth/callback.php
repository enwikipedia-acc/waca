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

// Change directory so we load files from the right place.
chdir("..");

require_once('config.inc.php');

global $siteConfiguration;
$application = new WebStart($siteConfiguration, new OAuthRequestRouter());

$application->run();
