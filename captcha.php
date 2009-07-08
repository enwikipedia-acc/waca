<?php

$ACC = 1;

if (!preg_match('/^\w+$/i',$_GET['id'])) {
	die('Invalid captcha id.');
}

session_start();
require_once 'includes/captcha.php';

$captcha = new captcha();
$captcha->doCaptcha($_GET['id']);

?>
