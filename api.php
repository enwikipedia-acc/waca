<?php
// API for helpmebot/other bots/etc
// This is a public-data-only read-only API, much in the same vein of ACCBot was.
//
// count - Displays statistics for the targeted user.
// status - Displays interface statistics, such as the number of open requests.
// stats - Gives a readout similar to the user list user information page.

require_once("config.inc.php");
require_once('functions.php');
require_once("includes/PdoDatabase.php");

$httpOrigin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;

$api = new Waca\API\Api($httpOrigin);

// get the request action, defaulting to help
$requestAction = "";
if (isset($_GET['action'])) {
	$requestAction = $_GET['action'];
}

echo $api->execute($requestAction);