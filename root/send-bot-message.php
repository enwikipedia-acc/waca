<?php

if (isset($_SERVER['REQUEST_METHOD'])) {
    die();
} //Web clients die.

require_once('../config.inc.php');
require_once('../functions.php');

$message = $argv[1];
// $formatted = formatforbot($message);
sendtobot($message);
