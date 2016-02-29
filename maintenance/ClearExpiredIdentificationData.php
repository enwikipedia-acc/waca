<?php
namespace Waca;

use Waca\ConsoleTasks\ClearExpiredIdentificationData;

chdir(__DIR__);
chdir('..');

require_once('config.inc.php');

global $siteConfiguration;
$application = new ConsoleStart($siteConfiguration, new ClearExpiredIdentificationData());

$application->run();
