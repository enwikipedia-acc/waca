<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

//This file is used by phpunit

$localconf = __DIR__ . '/../config.local.inc.php';

if (!file_exists($localconf)) {
    touch($localconf);
    file_put_contents($localconf, "<?php\n");
    file_put_contents($localconf, "\$toolserver_host = \"" . getenv('MYSQL_HOST') . "\";\n", FILE_APPEND);
    file_put_contents($localconf, "\$toolserver_database = \"" . getenv('MYSQL_SCHEMA') . "\";\n", FILE_APPEND);
    file_put_contents($localconf, "\$toolserver_username = \"" . getenv('MYSQL_USER') . "\";\n", FILE_APPEND);
    file_put_contents($localconf, "\$toolserver_password = \"" . getenv('MYSQL_PASSWORD') . "\";\n", FILE_APPEND);
}

// Load the config file for the autoloader.

require_once __DIR__ . '/../includes/SiteConfiguration.php';
require_once __DIR__ . '/../includes/Startup.php';
require_once __DIR__ . '/../includes/PdoDatabase.php';
require_once __DIR__ . '/../smarty-plugins/modifier.timespan.php';
require_once __DIR__ . '/../smarty-plugins/modifier.cidr.php';

require_once __DIR__ . '/utility/MockFunction.php';
require_once __DIR__ . '/utility/MockStaticMethod.php';
