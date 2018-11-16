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

class StatsIdUsers extends StatisticsPage
{
    protected function execute()
    {
        return $this->getUserList();
    }

    public function getPageTitle()
    {
        return "User identification status";
    }

    public function getPageName()
    {
        return "IdUsers";
    }

    public function isProtected()
    {
        return true;
    }

    private function getUserList()
    {
        global $currentIdentificationVersion, $forceIdentification;

        $query = <<<SQL
select username, status, checkuser, identified, case 
    when coalesce(identified, 0) = 0 then 'Not identified'
    when identified < ${forceIdentification} then 'Expired'
    when identified >= ${forceIdentification} and identified < ${currentIdentificationVersion} then 'About to expire'
    when identified = ${currentIdentificationVersion} then 'OK'
    else 'Unknown'
    end
from user 
where status in ('User', 'Admin', 'New')
order by username;
SQL;

        $qb = new QueryBrowser();
        $qb->rowFetchMode = PDO::FETCH_NUM;
        $qb->overrideTableTitles = array("User name", "Access level", "Checkuser?", "Version", "Identification status");
        $r = $qb->executeQueryToTable($query);

        return $r;
    }

    public function requiresWikiDatabase()
    {
        return false;
    }
}
