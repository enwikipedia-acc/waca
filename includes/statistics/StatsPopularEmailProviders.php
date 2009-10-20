<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/

class StatsPopularEmailProviders extends StatisticsPage
{
	function execute()
	{
		$qb = new QueryBrowser();
		$qb->numberedList = true;
		$qb->numberedListTitle = "Rank";
		$out=  $qb->executeQueryToTable("SELECT LOWER(SUBSTR(p.`pend_email`,INSTR(p.`pend_email`,'@')+1)) AS 'Domain', COUNT(*) AS 'Frequency' FROM acc_pend p GROUP BY LOWER(SUBSTR(p.`pend_email`,INSTR(p.`pend_email`,'@')+1)) ORDER BY COUNT(*) DESC LIMIT 100;");
		
		$out.= "<a name=\"tld\" ></a><h2>Top level domain frequency</h2>";
		
		$out.= $qb->executeQueryToTable("select lower(reverse( substring( reverse(pend_email), 1, instr( reverse(pend_email), '.' )-1 ) ) ) as 'Top-level domain', count(*) as 'frequency' from acc_pend group by lower(reverse( substring( reverse(pend_email), 1, instr( reverse(pend_email), '.' )-1 ) )) order by count(*) desc;");
		return $out;
	}
function getPageTitle(){return "Most Popular Email Providers";}
function getPageName(){return "PopularEmailProviders";}	
function isProtected(){return true;}
function requiresWikiDatabase(){return false;}
}