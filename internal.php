<?php
namespace Waca;

/*
 * Internal interface script
 *
 * THIS IS AN ENTRY POINT
 */

require_once('config.inc.php');

global $siteConfiguration;
$application = new WebStart($siteConfiguration);

$application->run();
