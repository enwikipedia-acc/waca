<?php
namespace Waca;

use Waca\Router\PublicRequestRouter;

/*
 * Public interface script
 *
 * THIS IS AN ENTRY POINT
 */

require_once('config.inc.php');

global $siteConfiguration;
$application = new WebStart($siteConfiguration, new PublicRequestRouter());

// This is a public interface
$application->setPublic(true);

$application->run();
