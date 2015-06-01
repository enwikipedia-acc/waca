<?php
//This file is used by phpunit

$localconf = __DIR__ . '/../config.local.inc.php';

if (!file_exists($localconf)) {
	touch($localconf);
	file_put_contents($localconf, "<?php\n");
	file_put_contents($localconf, "\$filepath = \"" . getcwd() . "/\";\n", FILE_APPEND);
	file_put_contents($localconf, "\$toolserver_host = \"" . getenv('MYSQL_HOST') . "\";\n", FILE_APPEND);
	file_put_contents($localconf, "\$toolserver_database = \"" . getenv('MYSQL_SCHEMA') . "\";\n", FILE_APPEND);
	file_put_contents($localconf, "\$toolserver_username = \"" . getenv('MYSQL_USER') . "\";\n", FILE_APPEND);
	file_put_contents($localconf, "\$toolserver_password = \"" . getenv('MYSQL_PASSWORD') . "\";\n", FILE_APPEND);
}

// Load the config file for the autoloader.
require_once __DIR__ . '/../config.inc.php';

require_once __DIR__ . '/../includes/PdoDatabase.php';
