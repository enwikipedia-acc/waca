<?php
namespace Waca;

require_once('config.inc.php');

global $siteConfiguration;
$application = new WebStart($siteConfiguration);

$application->run();