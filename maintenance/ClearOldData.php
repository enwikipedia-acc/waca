<?php
namespace Waca;

use Waca\ConsoleTasks\ClearOldDataTask;

chdir(__DIR__);
chdir('..');

require_once('config.inc.php');

global $siteConfiguration;
$application = new ConsoleStart($siteConfiguration, new ClearOldDataTask());

$application->run();