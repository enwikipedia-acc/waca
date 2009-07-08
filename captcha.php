<?php

$ACC = 1;

session_start();
require_once 'includes/captcha.php';

$captcha = new captcha();
$captcha->doCaptcha();

?>
