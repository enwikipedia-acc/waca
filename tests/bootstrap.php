<?php
//This file is used by phpunit

if(!file_exists(__DIR__ . '/../config.local.inc.php'))
{
    touch(__DIR__ . '/../config.local.inc.php'); 
}

// Load the config file for the autoloader.
require_once __DIR__ . '/../config.inc.php';