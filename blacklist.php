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

$nameblacklist[grawp1] = '/.*k*l[o0]?m[o0]?[i1]r*.*/i';
$nameblacklist[grawp2] = '/.*gr*(w|vv)p.*/i';
$nameblacklist[grawp3] = '/.*(hagg[ea]r|herme?y).*/i';
$nameblacklist[grawp4] = '/.*secret.*combination.*/i';
$nameblacklist[grawp5] = '/[-_\s]fuck[-_\s]/i';
$nameblacklist[upolicy1] = '/.*([4a]dm[1i]n|w[i1]k[1i]p[3e]d[1i][4a]|b[0o]t|st[3e]w[4a]rd|j[1i]mb[0o]).*/i';
$ipblacklist[example1] = '/127\.0\.0\..*/i';
?>
