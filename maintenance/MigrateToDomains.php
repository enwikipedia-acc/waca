<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

use Waca\ConsoleTasks\MigrateToDomains;

chdir(__DIR__);
chdir('..');

require_once('config.inc.php');

global $siteConfiguration;

// Override required schema version for this script
$siteConfiguration->setSchemaVersion(36);

$application = new ConsoleStart($siteConfiguration, new MigrateToDomains());

$application->run();
