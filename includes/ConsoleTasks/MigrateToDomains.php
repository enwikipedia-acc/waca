<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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
