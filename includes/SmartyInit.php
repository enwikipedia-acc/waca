<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

require_once 'lib/smarty/Smarty.class.php';

global $smarty, $smartydebug;
$smarty = new Smarty();

$toolVersion = getToolVersion();

$smarty->assign("tsurl", $tsurl);
$smarty->assign("wikiurl", $wikiurl);
$smarty->assign("toolversion", $toolVersion);
$smarty->debugging = $smartydebug;
?>
