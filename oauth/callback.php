<?php
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
