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
**                                                           **
**************************************************************/

/* A few (cut down list) from the Username blacklist on enwiki [[MediaWiki:Usernameblacklist]]
 * If anyone knows how to apply them to the blacklist below, feel free to do so - Stwalkerster 22:40 4 Jun 2008 (UTC)
    * (?i:peni[s5])
    * (?i:vagina)
    * (?i:cocksucker)
    * (?i:fu[c(k]k)
    * (\b[oO])(?i:n wheels)
    * (\b[sS])(?i:crotum)
    * (\b[oO])(?i:rgasm)
    * (?i:fellatio)
    * (?i:cunnilingus)
    * (?i:dildo)
    * 卍
    * 卐
    * [!?‽？]{3,}
    * (?i:ni(gg|qq)(a|er))
    * (?i:faggot)
*/





$nameblacklist[grawp1] = '/.*k*l[o0]?m[o0]?[i1]r*.*/i';
$nameblacklist[grawp2] = '/.*gr*(w|vv)p.*/i';
$nameblacklist[grawp3] = '/.*(hagg[ea]r|herme?y).*/i';
$nameblacklist[grawp4] = '/.*secret.*combination.*/i';
$nameblacklist[grawp5] = '/.*fuck.*/i';
$nameblacklist[grawp6] = '/.*t[3eh][3eh]_l[uo]lz/i';
$nameblacklist[grawp7] = '/k.a.l.o.m.i.r.a/';

$nameblacklist[grawp8] = '(?i:(g|9|q)r(a|4)(w|vv|?)(p|?))';

$nameblacklist[grawp9] = '(?i:p(w|vv|?)(a|4)r(g|9|q))';
$nameblacklist[upolicy1] = '/.*([4a]dm[1i]n|w[i1]k[1i]p[3e]d[1i][4a]|b[0o]t|st[3e]w[4a]rd|j[1i]mb[0o]).*/i';

//E-Mail Blacklist (not yet implemented in HEAD)
$emailblacklist[example1] = '/.&.*\@fake\.email$/i';

//DNSBLS
$dnsbls = Array	(
	'NJABL'	=> Array (
		'zone' => 'dnsbl.njabl.org',
		'bunk' => false,
		'url'  => 'http://www.njabl.org/cgi-bin/lookup.cgi?query=%i',
		'ret'  => Array (
			9	=> 'Open proxy',
			10	=> 'Open proxy'
		)
	),
	'IRCBL'	=> Array (
		'zone' => 'ircbl.ahbl.org',
		'bunk' => false,
		'url'  => 'http://www.ahbl.org/tools/lookup.php?ip=%i',
		'ret'  => Array (
			3	=> 'Open proxy',
			14	=> 'DDoS drone',
			15	=> 'Trojan',
			16	=> 'Virus',
			17	=> 'Malware',
			18	=> 'Ratware'
		)
	),
	'SECTOOR' => Array (
		'zone' => 'tor.dnsbl.sectoor.de',
		'bunk' => true,
		'url'  => 'http://www.sectoor.de/tor.php?ip=%i',
		'ret'  => Array (
			1	=> 'Tor exit server'
		)
	),
	'AHBL' => Array (
		'zone' => 'tor.ahbl.org',
		'bunk' => true,
		'url'  => 'http://www.ahbl.org/tools/lookup.php?ip=%i',
		'ret'  => Array (
			2	=> 'Tor exit server'
		)
	),
	'NoMoreFunn' => Array (
		'zone' => 'no-more-funn.moensted.dk',
		'bunk' => false,
		'url'  => 'http://moensted.dk/spam/no-more-funn?addr=%i',
		'ret'  => Array (
			10	=> 'Open proxy'
		)
	),
	'SORBS' => Array (
		'zone' => 'dnsbl.sorbs.net',
		'bunk' => false,
		'url'  => 'http://dnsbl.sorbs.net/cgi-bin/db?IP=%i',
		'ret'  => Array (
			2	=> 'Open HTTP Proxy',
			3	=> 'Open Socks Proxy',
			4	=> 'Other Open Proxy'
		)
	),
	'DSBL' => Array (
		'zone' => 'list.dsbl.org',
		'bunk' => false,
		'url'  => 'http://dsbl.org/listing?%i',
		'ret'  => Array (
			2	=> 'Open proxy'
		)
	),
	'XBL' => Array (
		'zone' => 'xbl.spamhaus.org',
		'bunk' => false,
		'url'  => 'http://www.spamhaus.org/query/bl?ip=%i',
		'ret'  => Array (
			4	=> 'CBL',
			5	=> 'NJABL',
			6	=> 'BOPM'
		)
	)
);
?>
