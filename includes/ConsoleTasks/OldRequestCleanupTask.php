<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Waca\Tasks\ConsoleTaskBase;

class OldRequestCleanupTask extends ConsoleTaskBase
{
    public function execute()
    {
        $statement = $this->getDatabase()->prepare(<<<SQL
            DELETE FROM request
            WHERE
                request.date < DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL :expiry DAY)
                AND request.emailconfirm != 'Confirmed'
                AND request.emailconfirm != ''
                AND NOT exists (SELECT 1 FROM comment c WHERE c.request = request.id)
SQL
        );

        $expiryTime = $this->getSiteConfiguration()->getEmailConfirmationExpiryDays();
        $statement->bindValue(':expiry', $expiryTime);
        $statement->execute();
    }
}