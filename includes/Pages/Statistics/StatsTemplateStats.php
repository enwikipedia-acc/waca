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
use Waca\Helpers\PreferenceManager;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class StatsTemplateStats extends InternalPageBase
{
    public function main()
    {
        $this->setHtmlTitle('Template Stats :: Statistics');

        $query = <<<SQL
SELECT
    t.id AS templateid,
    t.usercode AS usercode,
    activeUsers.count AS activecount,
    allUsers.count AS usercount
FROM welcometemplate t
LEFT JOIN
     (
         SELECT up.value as welcome_template, count(*) as count
         FROM user u
         INNER JOIN userpreference up ON u.id = up.user
         WHERE u.status = 'Active'
           AND up.domain = :domain1
           AND up.preference = :preference1
           AND up.value IS NOT NULL
         GROUP BY up.value
     ) activeUsers ON activeUsers.welcome_template = t.id
LEFT JOIN
     (
         SELECT up2.value as welcome_template, count(*) as count
         FROM user u2
         INNER JOIN userpreference up2 ON u2.id = up2.user
         WHERE up2.domain = :domain2
           AND up2.preference = :preference2
           AND up2.value IS NOT NULL
         GROUP BY up2.value
     ) allUsers ON allUsers.welcome_template = t.id
ORDER BY t.id
SQL;
        $database = $this->getDatabase();
        $statement = $database->prepare($query);
        $statement->execute([
            ':domain1' => WebRequest::getSessionDomain(),
            ':domain2' => WebRequest::getSessionDomain(),
            ':preference1' => PreferenceManager::PREF_WELCOMETEMPLATE,
            ':preference2' => PreferenceManager::PREF_WELCOMETEMPLATE,
        ]);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->assign('dataTable', $data);
        $this->assign('statsPageTitle', 'Template Stats');
        $this->setTemplate('statistics/welcome-template-usage.tpl');
    }
}
