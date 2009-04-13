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
**Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
**OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
**Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
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

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
}

$acrnamebl = array ();
$nameblacklist = array ();
$emailblacklist = array ();
$uablacklist = array ();

$acrnamebl['nigger'] = '/(?i:ni(gg|qq)(a|er))/';
$acrnamebl['grawp1'] = '/k*[l1][o0]?m[o0]?[i1]r*/i';
$acrnamebl['grawp2'] = '/[gq](r|rr)(aa|.)(w|v|vv|ww)p/i';
$acrnamebl['grawp3'] = '/(hagg[ea]r|herme?y|quarp)/i';
$acrnamebl['grawp4'] = '/secret.*combination/i';
$acrnamebl['grawp6'] = '/t[3eh][3eh]_l[uo]lz/i';
$acrnamebl['grawp7'] = '/k.[4a].[1l].[0o].m.[1i].r.[4a]/i';
$acrnamebl['grawp8'] = '/(need.to|will).*die/i';
$acrnamebl['grawp9'] = '/4chan/i';

$nameblacklist['nigger'] = '/(?i:ni(gg|qq)(a|er))/';
$nameblacklist['faggot'] = '/(?i:faggot)/';

$nameblacklist['grawp1'] = '/k*[l1][o0]?m[o0]?[i1]r*/i';
$nameblacklist['grawp2'] = '/[gq](r|rr)(aa|.)(w|v|vv|ww)p/i';
$nameblacklist['grawp3'] = '/(hagg[ea]r|herme?y|quarp)/i';
$nameblacklist['grawp4'] = '/secret.*combination/i';
$nameblacklist['grawp5'] = '/((ph|f)uc?k|shag)/i';
$nameblacklist['grawp6'] = '/t[3eh][3eh]_l[uo]lz/i';
$nameblacklist['grawp7'] = '/k.[4a].[1l].[0o].m.[1i].r.[4a]/i';
$nameblacklist['grawp8'] = '/(need.to|will).*die/i';
$nameblacklist['grawp9'] = '/4chan/i';
$nameblacklist['grawp10'] = '/wikipedo/i';
$nameblacklist['grawp11'] = '/pedophil/i';
$nameblacklist['grawp12'] = '/lolwut/i';
$nameblacklist['grawp14'] = '/(SteveCrossin|Mellie)/i';

$nameblacklist['upolicy4'] = '/.*([4a]dm[1i]n|w[i1]k[1i]p[3e]d[1i][4a]|st[3e]w[4a]rd|j[1i]mb[0o]).*/i';

//E-Mail Blacklist
$emailblacklist['grawp1'] = '/(shit|fuck|phuck)/i';
$emailblacklist['grawp2'] = '/^poo@.*/i';
$emailblacklist['grawp3'] = '/@poo\..*/i';
$emailblacklist['grawp4'] = '/@you\.com/i';
$emailblacklist['grawp5'] = '/pedo@.*/i';
$emailblacklist['grawp6'] = '/@youchans\.com/i';
$emailblacklist['grawp7'] = '/@ask\.com/i';
$emailblacklist['grawp8'] = '/@rawks\.com/i';
$emailblacklist['grawp9'] = '/(fuck|damn|shit|pedo)/i';
$emailblacklist['webring'] = '/webring@.*/i';


$emailblacklist['temporary-inboxes'] = "/temporaryinbox\.com/i";
$emailblacklist['temporary-inboxes-com'] = "/(maileater|10minutemail|2prong|4warding|6url|afrobacon|bugmenot|bumpymail|centermail|choicemail1|deadspam|despammed|discardmail|disposeamail|dodgeit|dontreg|dumpandjunk|e4ward|emailias|emailxfer|enterto|getonemail|gishpuppy|greensloth|guerrillamail|haltospam|jetable|kasmail|killmail|mail333|mailblocks|maileater|mailexpire|mailfreeonline|mailmoat|mailnull|mailshell|mailsiphon|mailzilla|mintemail|myspamless|mytrashmail|neomailbox|nobulk|noclickemail|netmails|oneoffemail|outlawspam|pancakemail|pookmail|punkass|rejectmail|sibmail|sneakemail|spamavert|spambob|spamslicer|spaml|spammotel|spamtrail|venompen|willselfdestruct|xemaps|xents|xmaily|yopmail|fakeinformation|fastacura|fastchevy|fastchrysler|fastkawasaki|fastmazda|fastmitsubishi|fastnissan|fastsubaru|fastsuzuki|fasttoyota|fastyamaha|fuckingduh|fux0ringduh|klassmaster|mailin8r|mailinator|mailinater|mailinator2|sogetthis |675hosting|amiriindustries|emailmiser|etranquil|gowikibooks|gowikicampus|gowikicars|gowikifilms|gowikigames|gowikimusic|gowikinetwork|gowikitravel|gowikitv|myspacepimpedup|ourklips|pimpedupmyspace|rklips|turual|upliftnow|uplipht|viditag|viewcastmedia|wetrainbayarea|xagloo|mailquack|mailslapping|oneoffmail|whopy|wilemail|spammotel|trashdevil|shiftmail|spambog|spamday|spamex|spamfree24|spamgourmet|spamhole|spamify|tempinbox|dotmsg|fakemailz|footard|forgetmail|lovemeleaveme|temporaryforwarding|temporaryinbox|trashmail|75hosting|myspaceinc)\.com/i";
$emailblacklist['temporary-inboxes-net'] = "/(centermail|emz|guerrillamail|jetable|killmail|klassmaster|wuzup|link2mail|nervmich|nervtmichmails|privacy|shortmail|spambob|spamfree24|tempemail|trashmail|675hosting|75hosting|ajaxapp|amiri|etranquil|iwi|myspaceinc|viewcastmedia)\.net/i";
$emailblacklist['temporary-inboxes-de'] = "/(discardmail|dontsendmespam|dumpmail|emaildienst|emailto|ghosttexter|hidemail|spambog|trashdevil|trashmail|trash-mail|twinmail|wegwerfadresse|spaminator|spamoff|temporarily|sofort-mail|safersignup|nurfuerspam|meinspamschutz|messagebeamer|netzidiot)\.de/i";
$emailblacklist['temporary-inboxes-org'] = "/(front14|h8s|hatespam|iheartspam|ipoo|jetable|mail2rss|spambob|oopi|poofy|spamcon|spamfree24|trashmail|wh4f|zoemail|675hosting|75hosting|etranquil|myspaceinc|viewcastmedia|wetrainbayarea|blogmyway|buyusedlibrarybooks)\.org/i";
$emailblacklist['temporary-inboxes-us'] = "/(nospamfor|spambox)\.us/i";
$emailblacklist['temporary-inboxes-dk'] = "/(anonymail|recyclemail|lortemail)\.dk/i";
$emailblacklist['temporary-inboxes-it'] = "/despam\.it/i";
$emailblacklist['temporary-inboxes-la'] = "/spam\.la/i";
$emailblacklist['whitehouse'] = "/whitehouse\.gov/i";


$uablacklist['grawp1'] = '/Mozilla\/4\.0 \(compatible; MSIE 7\.0; Windows NT 6\.0; SLCC1; \.NET CLR 2\.0\.50727; \.NET CLR 3\.0\.04506; InfoPath\.2; \.NET CLR 3\.5\.21022\)/';

//DNSBLS
$dnsbls = array (
	'IRCBL' => array (
		'zone' => 'ircbl.ahbl.org',
		'bunk' => false,
		'url' => 'http://www.ahbl.org/tools/lookup.php?ip=%i',
		'ret' => array (
			3 => 'Open proxy',
			14 => 'DDoS drone',
			15 => 'Trojan',
			16 => 'Virus',
			17 => 'Malware',
			18 => 'Ratware'
		)
	),
	'SECTOOR' => array (
		'zone' => 'tor.dnsbl.sectoor.de',
		'bunk' => true,
		'url' => 'http://www.sectoor.de/tor.php?ip=%i',
		'ret' => array (
			1 => 'Tor exit server'
		)
	),
	'AHBL' => array (
		'zone' => 'tor.ahbl.org',
		'bunk' => true,
		'url' => 'http://www.ahbl.org/tools/lookup.php?ip=%i',
		'ret' => array (
			2 => 'Tor exit server'
		)
	),
	'NoMoreFunn' => array (
		'zone' => 'no-more-funn.moensted.dk',
		'bunk' => false,
		'url' => 'http://moensted.dk/spam/no-more-funn?addr=%i',
		'ret' => array (
			10 => 'Open proxy'
		)
	),
	'SORBS' => array (
		'zone' => 'dnsbl.sorbs.net',
		'bunk' => false,
		'url' => 'http://dnsbl.sorbs.net/cgi-bin/db?IP=%i',
		'ret' => array (
			2 => 'Open HTTP Proxy',
			3 => 'Open Socks Proxy',
			4 => 'Other Open Proxy'
		)
	),
	'DSBL' => array (
		'zone' => 'list.dsbl.org',
		'bunk' => false,
		'url' => 'http://dsbl.org/listing?%i',
		'ret' => array (
			2 => 'Open proxy'
		)
	),
	'XBL' => array (
		'zone' => 'xbl.spamhaus.org',
		'bunk' => false,
		'url' => 'http://www.spamhaus.org/query/bl?ip=%i',
		'ret' => array (
/*			4 => 'CBL', */
			6 => 'BOPM'
		)
	)
);
function checkdnsbls($addr) {
	global $dnsbls;

	$dnsblip = implode('.', array_reverse(explode('.', $addr)));
	$dnsbldata = '<ul>';
	$banned = false;

	foreach ($dnsbls as $dnsblname => $dnsbl) {
		echo '<!-- Checking ' . $dnsblname . ' ... ';
		$tmpdnsblresult = gethostbyname($dnsblip . '.' . $dnsbl['zone']);
		echo $tmpdnsblresult . ' -->';
		if (long2ip(ip2long($tmpdnsblresult)) != $tmpdnsblresult) {
			$tmpdnsblresult = 'Nothing.';
			continue;
		}
		//		if (!isset($dnsbl['ret'][$lastdigit]) and ($dnsbl['bunk'] == false)) { $tmpdnsblresult = 'Nothing.'; continue; }
		$dnsbldata .= '<li> ' . $dnsblip . '.' . $dnsbl['zone'] . ' (' . $dnsblname . ') = ' . $tmpdnsblresult;
		$lastdigit = explode('.', $tmpdnsblresult);
		$lastdigit = $lastdigit['3'];
		if (isset ($dnsbl['ret'][$lastdigit])) {
			$dnsbldata .= ' (' . $dnsbl['ret'][$lastdigit] . ')';
			$banned = true;
		} else {
			$dnsbldata .= ' (unknown)';
			if ($dnsbl['bunk'])
				$banned = true;
		}
		$dnsbldata .= ' &mdash;  <a href="' . str_replace('%i', $addr, $dnsbl['url']) . "\"> more information</a>.\n";
	}
	unset ($dnsblip, $dnsblname, $dnsbl, $tmpdnsblresult, $lastdigit);

	$dnsbldata .= '</ul>';
	echo '<!-- ' . $dnsbldata . ' -->';
	return array (
		$banned,
		$dnsbldata
	);
}
?>
