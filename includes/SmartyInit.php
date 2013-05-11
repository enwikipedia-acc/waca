<?php
require_once 'lib/smarty-3.1.13/Smarty.class.php';

global $smarty;
$smarty = new Smarty();

$toolVersion = getToolVersion();

$smarty->assign("tsurl", $tsurl);
$smarty->assign("toolversion", $toolVersion);
?>