<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca;

use Waca\ConsoleTasks\AutoFlagCommentsTask;

require_once(__DIR__ . '/../includes/Startup.php');

global $siteConfiguration;
$application = new ConsoleStart($siteConfiguration, new AutoFlagCommentsTask());

$application->run();