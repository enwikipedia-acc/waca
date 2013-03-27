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

class StatsSigCheck extends StatisticsPage
{
        function execute()
        {
                ini_set("user_agent", "ACC/1.0 (+acc@toolserver.org)");

                $query = <<<QUERY
select user_name, user_onwikiname, user_welcome_sig from acc_user where user_welcome_templateid != 0 and (user_level = "Admin" or user_level = "User");
QUERY;
                global $tsurl;
                $qb = new QueryBrowser();
                $qb->rowFetchMode = MYSQL_ASSOC;
                $qb->tableCallbackFunction = "statsSigCheckRowCallback";
                $qb->overrideTableTitles = array("User name", "enwiki name", "signature code", "rendered signature");
                $r = $qb->executeQueryToTable($query);
                echo mysql_error();

                return $r;
        }
        function getPageName()
        {
                return "SigCheck";
        }
        function getPageTitle()
        {
                return "SigCheck";
        }
        function isProtected()
        {
                return true;
        }

        function requiresWikiDatabase()
        {
                return false;
        }
}

function statsSigCheckRowCallback($row, $currentreq)
{
        $out.=  '<tr';
        if ($currentreq % 2 == 0) {
                $out.=  ' class="alternate">';
        } else {
                $out.=  '>';
        }

        $botsig = welcomerbotRenderSig( $row["user_onwikiname"], $row["user_welcome_sig"] );

        $out .= "<td>" . $row["user_name"] . "</td><td>" . $row["user_onwikiname"] . "</td><td>" . htmlentities($botsig,ENT_COMPAT,'UTF-8') . "</td><td>";
		
        $apiresult = file_get_contents("http://en.wikipedia.org/w/api.php?action=parse&disablepp&prop=text&format=php&text=" . urlencode(trim($botsig)));
        $renderedraw = unserialize($apiresult);

        $out .= $renderedraw["parse"]["text"]["*"];

        $out.="</td></tr>\n";

        return $out;
}
