<?php

$toolList = array(
	'tgt' => '//toolserver.org/~stwalkerster/referrer/target.php?ip=%DATA%',
);

if(isset($_GET['round2']))
{
	echo '<script>window.location.href="'.str_replace("%DATA%", $_GET['data'], $toolList[$_GET['tool']]).'"</script>';
}
else
{
	header("Location: " . $_SERVER["REQUEST_URI"] . "&round2=true");
}
