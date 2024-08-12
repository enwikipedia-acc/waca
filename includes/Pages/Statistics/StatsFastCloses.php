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

class StatsFastCloses extends InternalPageBase
{
    public function main()
    {
        $this->setHtmlTitle('Fast Closes :: Statistics');

        $query = <<<SQL
WITH closedescs AS (
    SELECT closes, mail_desc FROM closes
    UNION ALL
    SELECT 'EnqueuedJobQueue', 'Queued for creation'
)
SELECT
  log_closed.objectid AS request,
  user.username AS user,
  user.id AS userid,
  TIMEDIFF(log_closed.timestamp, log_reserved.timestamp) AS timetaken,
  closedescs.mail_desc AS closetype,
  log_closed.timestamp AS date

FROM log log_closed
INNER JOIN log log_reserved ON log_closed.objectid = log_reserved.objectid 
	AND log_closed.objecttype = log_reserved.objecttype
LEFT JOIN closedescs ON closedescs.`closes` = log_closed.action
LEFT JOIN user ON log_closed.user = user.id

WHERE ( log_closed.action LIKE 'Closed%' OR log_closed.action = 'EnqueuedJobQueue' )
  AND log_reserved.action = 'Reserved'
  AND TIMEDIFF(log_closed.timestamp, log_reserved.timestamp) < '00:00:30'
  AND log_closed.user = log_reserved.user
  AND TIMEDIFF(log_closed.timestamp, log_reserved.timestamp) > '00:00:00'
  AND DATE(log_closed.timestamp) > DATE(NOW()-INTERVAL 3 MONTH)

ORDER BY TIMEDIFF(log_closed.timestamp, log_reserved.timestamp) ASC
;
SQL;
        $database = $this->getDatabase();
        $statement = $database->query($query);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->assign('dataTable', $data);
        $this->assign('statsPageTitle', 'Requests closed less than 30 seconds after reservation in the past 3 months');
        $this->setTemplate('statistics/fast-closes.tpl');
    }
}
