<?php
namespace Waca\Pages\Statistics;

use PDO;
use Waca\SecurityConfiguration;
use Waca\StatisticsPage;

class StatsTemplateStats extends StatisticsPage
{
	protected function executeStatisticsPage()
	{
		$query = <<<SQL
SELECT
    t.id as templateid,
    t.usercode as usercode,
    u.count as activecount,
    countall as usercount
FROM welcometemplate t
    LEFT JOIN
    (
        SELECT
            welcome_template,
            COUNT(*) as count
        FROM user
        WHERE
            (status = 'User' OR status = 'Admin')
            AND welcome_template IS NOT NULL
        GROUP BY welcome_template
    ) u ON u.welcome_template = t.id
    LEFT JOIN
    (
        SELECT
            welcome_template as allid,
            COUNT(*) as countall
        FROM user
        WHERE welcome_template IS NOT NULL
        GROUP BY welcome_template
    ) u2 ON u2.allid = t.id;
SQL;
		$database = gGetDb();
		$statement = $database->query($query);
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		$this->assign('dataTable', $data);
		$this->assign('statsPageTitle','Template Stats');
		$this->setTemplate('statistics/welcome-template-usage.tpl');
	}

	public function getPageTitle()
	{
		return "Template Stats";
	}

	public function getSecurityConfiguration()
	{
		return SecurityConfiguration::internalPage();
	}
}
