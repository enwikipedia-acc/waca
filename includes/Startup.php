<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

chdir(__DIR__ . '/..');

require_once 'includes/AutoLoader.php';
spl_autoload_register('Waca\\AutoLoader::load');
require_once 'vendor/autoload.php';

$siteConfiguration = new SiteConfiguration();

require_once 'config.inc.php';
