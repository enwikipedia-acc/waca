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

class StatsTemplateStats extends InternalPageBase
{
    public function main()
    {
        $this->setHtmlTitle('Template Stats :: Statistics');

        $query = <<<SQL
SELECT
    t.id AS templateid,
    t.usercode AS usercode,
    u.count AS activecount,
    countall AS usercount
FROM welcometemplate t
    LEFT JOIN
    (
        SELECT
            welcome_template,
            COUNT(*) AS count
        FROM user
        WHERE
            (status = 'User' OR status = 'Admin')
            AND welcome_template IS NOT NULL
        GROUP BY welcome_template
    ) u ON u.welcome_template = t.id
    LEFT JOIN
    (
        SELECT
            welcome_template AS allid,
            COUNT(*) AS countall
        FROM user
        WHERE welcome_template IS NOT NULL
        GROUP BY welcome_template
    ) u2 ON u2.allid = t.id;
SQL;
        $database = $this->getDatabase();
        $statement = $database->query($query);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->assign('dataTable', $data);
        $this->assign('statsPageTitle', 'Template Stats');
        $this->setTemplate('statistics/welcome-template-usage.tpl');
    }
}
