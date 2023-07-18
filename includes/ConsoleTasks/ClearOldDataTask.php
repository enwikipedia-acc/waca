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

        $query = $this->getDatabase()->prepare(<<<SQL
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

        $dataQuery = $this->getDatabase()->prepare(<<<SQL
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
    }
}