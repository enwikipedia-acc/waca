<?php
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
