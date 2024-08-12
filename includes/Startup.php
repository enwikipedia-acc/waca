<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca;

chdir(__DIR__ . '/..');

require_once 'includes/AutoLoader.php';
spl_autoload_register('Waca\\AutoLoader::load');
require_once 'vendor/autoload.php';

global $siteConfiguration;
$siteConfiguration = new SiteConfiguration();

require_once 'config.inc.php';
