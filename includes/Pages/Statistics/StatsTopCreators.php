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
use Waca\DataObjects\EmailTemplate;
use Waca\Tasks\InternalPageBase;

class StatsTopCreators extends InternalPageBase
{
    public function main()
    {
        $this->setHtmlTitle('Top Creators :: Statistics');

        // Retrieve all-time stats
        $queryAllTime = <<<SQL
SELECT
	/* StatsTopCreators::execute()/queryAllTime */
    COUNT(*) count,
    user.username username,
    user.status status,
    user.id userid
FROM log
LEFT JOIN emailtemplate ON concat('Closed ', emailtemplate.id) = log.action
INNER JOIN user ON user.id = log.user
WHERE emailtemplate.defaultaction = :created
   OR log.action = 'Closed custom-y'

GROUP BY log.user, user.username, user.status
ORDER BY COUNT(*) DESC;
SQL;

        // Retrieve all-time stats for active users only
        $queryAllTimeActive = <<<SQL
SELECT
	/* StatsTopCreators::execute()/queryAllTimeActive */
    COUNT(*) count,
    user.username username,
    user.status status,
    user.id userid
FROM log
LEFT JOIN emailtemplate ON concat('Closed ', emailtemplate.id) = log.action
INNER JOIN user ON user.id = log.user
WHERE
	(emailtemplate.defaultaction = :created OR log.action = 'Closed custom-y')
    AND user.status != 'Deactivated'
GROUP BY user.username, user.id
ORDER BY COUNT(*) DESC;
SQL;

        // Retrieve today's stats (so far)
        $queryToday = <<<SQL
SELECT
	/* StatsTopCreators::execute()/top5out */
    COUNT(*) count,
    user.username username,
    user.status status,
    user.id userid
FROM log
INNER JOIN user ON user.id = log.user
LEFT JOIN emailtemplate ON CONCAT('Closed ', emailtemplate.id) = log.action
WHERE (emailtemplate.defaultaction = :created OR log.action = 'Closed custom-y')
  AND log.timestamp BETWEEN CURRENT_DATE() AND NOW()
GROUP BY log.user, user.username
ORDER BY COUNT(*) DESC;
SQL;

        // Retrieve Yesterday's stats
        $queryYesterday = <<<SQL
SELECT
	/* StatsTopCreators::execute()/top5yout */
    COUNT(*) count,
    user.username username,
    user.status status,
    user.id userid
FROM log
INNER JOIN user ON user.id = log.user
LEFT JOIN emailtemplate ON CONCAT('Closed ', emailtemplate.id) = log.action
WHERE (emailtemplate.defaultaction = :created OR log.action = 'Closed custom-y')
  AND log.timestamp BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY) AND CURRENT_DATE()
GROUP BY log.user, user.username
ORDER BY COUNT(*) DESC;
SQL;

        // Retrieve last 7 days
        $queryLast7Days = <<<SQL
SELECT
	/* StatsTopCreators::execute()/top5wout */
    COUNT(*) count,
    user.username username,
    user.status status,
    user.id userid
FROM log
INNER JOIN user ON user.id = log.user
LEFT JOIN emailtemplate ON CONCAT('Closed ', emailtemplate.id) = log.action
WHERE (emailtemplate.defaultaction = :created OR log.action = 'Closed custom-y')
  AND log.timestamp BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()
GROUP BY log.user, user.username
ORDER BY COUNT(*) DESC;
SQL;

        // Retrieve last month's stats
        $queryLast28Days = <<<SQL
SELECT
	/* StatsTopCreators::execute()/top5mout */
    COUNT(*) count,
    user.username username,
    user.status status,
    user.id userid
FROM log
INNER JOIN user ON user.id = log.user
LEFT JOIN emailtemplate ON CONCAT('Closed ', emailtemplate.id) = log.action
WHERE (emailtemplate.defaultaction = :created OR log.action = 'Closed custom-y')
  AND log.timestamp BETWEEN DATE_SUB(NOW(), INTERVAL 28 DAY) AND NOW()
GROUP BY log.user, user.username
ORDER BY COUNT(*) DESC;
SQL;

        // Put it all together
        $queries = array(
            'queryAllTime'       => $queryAllTime,
            'queryAllTimeActive' => $queryAllTimeActive,
            'queryToday'         => $queryToday,
            'queryYesterday'     => $queryYesterday,
            'queryLast7Days'     => $queryLast7Days,
            'queryLast28Days'    => $queryLast28Days,
        );

        $database = $this->getDatabase();
        foreach ($queries as $name => $sql) {
            $statement = $database->prepare($sql);
            $statement->execute([":created" => EmailTemplate::ACTION_CREATED]);
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            $this->assign($name, $data);
        }

        $this->assign('statsPageTitle', 'Top Account Creators');
        $this->setTemplate('statistics/top-creators.tpl');
    }
}
