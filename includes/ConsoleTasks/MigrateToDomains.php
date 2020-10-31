<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Waca\DataObjects\Domain;
use Waca\DataObjects\RequestQueue;
use Waca\Tasks\ConsoleTaskBase;

class MigrateToDomains extends ConsoleTaskBase
{
    public function execute()
    {
        echo "This migration script must be run with the entire application at an earlier version.";
    }
}
