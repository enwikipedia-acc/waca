<?php
/**************************************************************
** English Wikipedia Account Request Interface               **
** Wikipedia Account Request Graphic Design by               **
** Charles Melbye is licensed under a Creative               **
** Commons Attribution-Noncommercial-Share Alike             **
** 3.0 United States License. All other code                 **
** released under Public Domain by the ACC                   **
** Development Team.                                         **
**             Developers:                                   **
** SQL ( http://en.wikipedia.org/User:SQL )                 **
** Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
** FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
** Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
** Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
** Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
** OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
** Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
** Chris G ( http://en.wikipedia.org/wiki/User:Chris_G )      **
**                                                           **
**************************************************************/

// Get all the classes.
require_once 'config.inc.php';
require_once 'includes/imagegen.php';

// Initialize the class object.
$imagegen = new imagegen();

// Set the variables of the generated image.
$name = $_GET['name'];
$text = $_GET['text'];

$name= escapeshellcmd($name);

if (!preg_match('/^[0-9A-Za-z]*$/',$name)) {
	die('specify alphanumeric name');
}

// Generate the image and write a copy to the filesystem.
$imagegen->create($name, $text);

// Display the image on the screen.
echo '<img src="images/' . $name . '.png" alt="' . $text . '" /> ';
?>