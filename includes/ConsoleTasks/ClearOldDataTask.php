<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Exception;
use Waca\Tasks\ConsoleTaskBase;

class ClearOldDataTask extends ConsoleTaskBase
{
    public function execute()
    {
        $dataClearInterval = $this->getSiteConfiguration()->getDataClearInterval();
        $database = $this->getDatabase();

        $query = $database->prepare(<<<SQL
UPDATE request
SET ip = :ip, forwardedip = null, email = :mail, useragent = ''
WHERE date < DATE_SUB(curdate(), INTERVAL {$dataClearInterval})
AND status = 'Closed';
SQL
        );

        $success = $query->execute(array(
            ":ip"   => $this->getSiteConfiguration()->getDataClearIp(),
            ":mail" => $this->getSiteConfiguration()->getDataClearEmail(),
        ));

        if (!$success) {
            throw new Exception("Error in transaction 1: Could not clear data.");
        }

        $dataQuery = $database->prepare(<<<SQL
DELETE rd
FROM requestdata rd
INNER JOIN request r ON r.id = rd.request
WHERE r.date < DATE_SUB(curdate(), INTERVAL {$dataClearInterval})
  AND r.status = 'Closed';
SQL
        );

        $success = $dataQuery->execute();

        if (!$success) {
            throw new Exception("Error in transaction 2: Could not clear data.");
        }

        // FIXME: domains!
        $flaggedCommentsQuery = $database->query(<<<SQL
SELECT COUNT(1) FROM comment c INNER JOIN request r ON c.request = r.id WHERE c.flagged = 1 AND r.status = 'Closed'
SQL
        );

        $flaggedCommentsCount = $flaggedCommentsQuery->fetchColumn();
        $this->getNotificationHelper()->alertFlaggedComments($flaggedCommentsCount);
    }
}