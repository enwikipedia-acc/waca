<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Waca\IdentificationVerifier;
use Waca\Tasks\ConsoleTaskBase;

class ClearExpiredIdentificationData extends ConsoleTaskBase
{
    /**
     * @return void
     */
    public function execute()
    {
        IdentificationVerifier::clearExpiredCacheEntries($this->getSiteConfiguration(), $this->getDatabase());
    }
}
