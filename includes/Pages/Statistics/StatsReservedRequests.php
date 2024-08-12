<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Pages\Statistics;

use PDO;
use Waca\Tasks\InternalPageBase;

class StatsReservedRequests extends InternalPageBase
{
    public function main()
    {
        $this->setHtmlTitle('Reserved Requests :: Statistics');

        $query = <<<sql
SELECT
    p.id AS requestid,
    p.name AS name,
    p.status AS status,
    u.username AS user,
    u.id AS userid
FROM request p
    INNER JOIN user u ON u.id = p.reserved
WHERE reserved != 0;
sql;

        $database = $this->getDatabase();
        $statement = $database->query($query);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->assign('dataTable', $data);
        $this->assign('statsPageTitle', 'All currently reserved requests');
        $this->setTemplate('statistics/reserved-requests.tpl');
    }
}
