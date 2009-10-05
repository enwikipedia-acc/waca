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
**  SQL ( http://en.wikipedia.org/User:SQL )                 **
**  Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
**FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
**Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
**Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
**Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
**OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
**Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
**                                                           **
**************************************************************/

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client.

$regdevlist = array (
	array (
		'SQL',
		'SQL',
		'1'
	),
	array (
		'Cobi',
		'Cobi',
		'64'
	),
	array (
		'charlie',
		'Cmelbye',
		'67'
	),
	array (
		'FastLizard4',
		'FastLizard4',
		'18'
	),
	array (
		'Stwalkerster',
		'Stwalkerster',
		'7'
	),
	array (
		'X!',
		'X!',
		'4'
	),
	array (
		'Alexfusco5',
		'Alexfusco5',
		'34'
	),
	array (
		'OverlordQ',
		'OverlordQ',
		'36'
	),
	array (
		'Prodego',
		'Prodego',
		'14'
	),
	array (
		'FunPika',
		'FunPika',
		'38'
	),
  	array (
		'Prom3th3an',
		'Promethean',
		'91'
	),
	array (
		'Chris',
		'Chris G',
		'20'
	),
	array (
		'LouriePieterse',
		'LouriePieterse',
		'556'
	)
);
//Format: User on tool, user on wiki, user id

$ircdevlist = array (
	'SQLDb',
	'Cobi',
	'Cobi-Laptop',
	'chuck',
	'charlie-',
	'FastLizard4',
	'stwalkerster',
	'|X|',
	'Alexfusco5',
	'Alexfusco5|Away',
	'OverlordQ',
	'Prodego',
	'FunPika',
	'Prom_cat',
	'Prom3th3an',
	'Chris_G',
	'LouriePieterse'
);
?>
