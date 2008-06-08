<?PHP
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
**                                                           **
**************************************************************/

/* A few (cut down list) from the Username blacklist on enwiki [[MediaWiki:Usernameblacklist]]
 
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
*/    
//$nameblacklist[upolicy1] = '/卍/';
//$nameblacklist[upolicy2] = '/卐/';  // These screw up the preg parser.
//$nameblacklist[upolicy3] = '/[!?‽？]{3,}/';

if($ACC != "1") { 
        header("Location: http://toolserver.org/~sql/acc/");
	die();
}

$acrnamebl[nigger]   = '/(?i:ni(gg|qq)(a|er))/';
$acrnamebl[grawp1]   = '/k*[l1][o0]?m[o0]?[i1]r*/i';
$acrnamebl[grawp2]   = '/[gq](r|rr)(aa|.)(w|v|vv|ww)p/i';
$acrnamebl[grawp3]   = '/(hagg[ea]r|herme?y|quarp)/i';
$acrnamebl[grawp4] = '/secret.*combination/i';
$acrnamebl[grawp5] = '/((ph|f)uc?k|s[e3]x|shag)/i';
$acrnamebl[grawp6] = '/t[3eh][3eh]_l[uo]lz/i';
$acrnamebl[grawp7] = '/k.[4a].[1l].[0o].m.[1i].r.[4a]/i';

$nameblacklist[nigger]   = '/(?i:ni(gg|qq)(a|er))/';
$nameblacklist[faggot]   = '/(?i:faggot)/';


$nameblacklist[grawp1] = '/k*[l1][o0]?m[o0]?[i1]r*/i';
$nameblacklist[grawp2] = '/[gq](r|rr)(aa|.)(w|v|vv|ww)p/i';
$nameblacklist[grawp3] = '/(hagg[ea]r|herme?y|quarp)/i';
$nameblacklist[grawp4] = '/secret.*combination/i';
$nameblacklist[grawp5] = '/((ph|f)uc?k|s[e3]x|shag)/i';
$nameblacklist[grawp6] = '/t[3eh][3eh]_l[uo]lz/i';
$nameblacklist[grawp7] = '/k.[4a].[1l].[0o].m.[1i].r.[4a]/i';

#$nameblacklist[grawp8] = '/(?i:(g|9|q)r(a|4)(w|vv|.)(p|.))/i';

#$nameblacklist[grawp9] = '(?i:p(w|vv|?)(a|4)r(g|9|q))';
$nameblacklist[upolicy4] = '/.*([4a]dm[1i]n|w[i1]k[1i]p[3e]d[1i][4a]|b[0o]t|st[3e]w[4a]rd|j[1i]mb[0o]).*/i';

//E-Mail Blacklist
$emailblacklist[grawp1] = '/(shit|fuck|sex|phuck)/i';
$emailblacklist[grawp2] = '/^poo@.*/i';
$emailblacklist[grawp3] = '/@poo\..*/i';

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
function checkdnsbls ($addr) {
	global $dnsbls;

	$dnsblip = implode('.',array_reverse(explode('.',$addr)));
	$dnsbldata = '<ul>';
	$banned = false;

	foreach ($dnsbls as $dnsblname => $dnsbl) {
		echo '<!-- Checking '.$dnsblname.' ... ';
		$tmpdnsblresult = gethostbyname($dnsblip.'.'.$dnsbl['zone']);
		echo $tmpdnsblresult.' -->';
		if (long2ip(ip2long($tmpdnsblresult)) != $tmpdnsblresult) { $tmpdnsblresult = 'Nothing.'; continue; }
//		if (!isset($dnsbl['ret'][$lastdigit]) and ($dnsbl['bunk'] == false)) { $tmpdnsblresult = 'Nothing.'; continue; }
		$dnsbldata .= '<li> '.$dnsblip.'.'.$dnsbl['zone'].' ('.$dnsblname.') = '.$tmpdnsblresult;
		$lastdigit = explode('.',$tmpdnsblresult);
		$lastdigit = $lastdigit[3];
		if (isset($dnsbl['ret'][$lastdigit])) { $dnsbldata .= ' ('.$dnsbl['ret'][$lastdigit].')'; $banned = true; }
		else { $dnsbldata .= ' (unknown)'; if ($dnsbl['bunk']) $banned = true; }
		$dnsbldata .= ' &mdash;  <a href="'.str_replace('%i',$addr,$dnsbl['url'])."\"> more information</a>.\n";
	}
	unset($dnsblip,$dnsblname,$dnsbl,$tmpdnsblresult,$lastdigit);

	$dnsbldata .= '</ul>';
	echo '<!-- '.$dnsbldata.' -->';
	return array($banned,$dnsbldata);
}
?>
