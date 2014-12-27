<?php
//This file is used by phpunit

$localconf = __DIR__ . '/../config.local.inc.php';

if(!file_exists($localconf))
{
    touch($localconf);
}

file_put_contents($localconf, "<?php\n\$filepath = \"" . getcwd() . "/\";\n");

// Load the config file for the autoloader.
require_once __DIR__ . '/../config.inc.php';

require_once __DIR__ . '/../includes/PdoDatabase.php';
