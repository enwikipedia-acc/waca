<?php
namespace Waca\Pages\Statistics;

use PDO;
use Waca\PageBase;
use Waca\SecurityConfiguration;

class StatsTemplateStats extends PageBase
{
	public function main()
	{
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

	public function getSecurityConfiguration()
	{
		return SecurityConfiguration::internalPage();
	}
}
