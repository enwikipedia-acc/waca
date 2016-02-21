<?php
/**
 * @deprecated
 */

if (!defined("ACC")) {
	die();
} // Invalid entry point

global $smarty, $smartydebug;
$smarty = new Smarty();

$toolVersion = Waca\Environment::getToolVersion();
$currentUser = User::getCurrent();

$smarty->assign("baseurl", $baseurl);
$smarty->assign("mediawikiScriptPath", $mediawikiScriptPath);
$smarty->assign("toolversion", $toolVersion);
$smarty->assign("currentUser", $currentUser);
$smarty->debugging = $smartydebug;
