<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

use Waca\ConsoleTasks\RefreshOAuthDataTask;

chdir(__DIR__);
chdir('..');

require_once('config.inc.php');

global $siteConfiguration;
$application = new ConsoleStart($siteConfiguration, new RefreshOAuthDataTask());

$application->run();