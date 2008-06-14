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
**                                                           **
**************************************************************/

$regdevlist = array('SQL', 'Cobi', 'Charlie', 'FastLizard4', 'Stwalkerster', 'Soxred93', 'Alexfusco5');
$ircdevlist = array('SQLDb', 'Cobi', 'Cobi-Laptop', 'chuck', 'FastLizard4', 'stwalkerster', 'Soxred93', 'Alexfusco5', 'Alexfusco5|Away');

// Users
	//	Nick!User@Host mask						=> group
	$users = array(
		'Cobi!*cobi*@cobi.cluenet.org'					=> 'root',
		'Cobi-Laptop!*@2002:1828:8399:4000:21f:3bff:fe10:4ae3'		=> 'root',
		'|Cobi|!*@2002:1828:8399:4000:21f:3bff:fe10:4ae3'		=> 'root',
		'SQLDb!*@wikipedia/SQL'						=> 'root',
		'Stwalkerster*!*@wikipedia/Stwalkerster'			=> 'developer',
		'Alexfusco5!*@wikimedia/Alexfusco5'				=> 'developer',
		'Soxred93!*@unaffiliated/soxred93'				=> 'developer',
		'*!*@wikipedia/FastLizard4'               => 'developer',
		'*!*@*'								=> '*'
		);

?>
