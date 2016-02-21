<?php
namespace Waca;

use Waca\ConsoleTasks\ClearOAuthDataTask;

chdir(__DIR__);
chdir('..');

require_once('config.inc.php');

global $siteConfiguration;
$application = new ConsoleStart($siteConfiguration, new ClearOAuthDataTask());

$application->run();