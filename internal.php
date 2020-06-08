<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

use Waca\Router\RequestRouter;

/*
 * Internal interface script
 *
 * THIS IS AN ENTRY POINT
 */

require_once('config.inc.php');

global $siteConfiguration;
$application = new WebStart($siteConfiguration, new RequestRouter());

$application->run();
