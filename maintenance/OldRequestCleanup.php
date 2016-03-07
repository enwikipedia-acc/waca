<?php
namespace Waca;

use Waca\ConsoleTasks\OldRequestCleanupTask;

chdir(__DIR__);
chdir('..');

require_once('config.inc.php');

global $siteConfiguration;
$application = new ConsoleStart($siteConfiguration, new OldRequestCleanupTask());

$application->run();