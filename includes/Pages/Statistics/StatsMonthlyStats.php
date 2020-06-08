<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages\Statistics;

use PDO;
use Waca\Tasks\InternalPageBase;

class StatsMonthlyStats extends InternalPageBase
{
    public function main()
    {
        $this->setHtmlTitle('Monthly Stats :: Statistics');

        $query = <<<SQL
SELECT
    COUNT(DISTINCT id) AS closed,
    YEAR(timestamp) AS year,
    MONTHNAME(timestamp) AS month
FROM log /* StatsMonthlyStats */
WHERE action LIKE 'Closed%'
GROUP BY EXTRACT(YEAR_MONTH FROM timestamp)
ORDER BY YEAR(timestamp) , MONTH(timestamp) ASC;
SQL;

        $database = $this->getDatabase();
        $statement = $database->query($query);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->assign('dataTable', $data);
        $this->assign('statsPageTitle', 'Monthly Statistics');
        $this->setTemplate('statistics/monthly-stats.tpl');
    }
}
