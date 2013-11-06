<?php
require_once 'lib/smarty/Smarty.class.php';

global $smarty, $smartydebug;
$smarty = new Smarty();

$toolVersion = getToolVersion();

$smarty->assign("tsurl", $tsurl);
$smarty->assign("toolversion", $toolVersion);
$smarty->debugging = $smartydebug;
?>
