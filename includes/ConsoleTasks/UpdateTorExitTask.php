<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Waca\Providers\TorExitProvider;
use Waca\Tasks\ConsoleTaskBase;

class UpdateTorExitTask extends ConsoleTaskBase
{
    /**
     * @return void
     */
    public function execute()
    {
        TorExitProvider::regenerate(
            $this->getDatabase(),
            $this->getHttpHelper(),
            $this->getSiteConfiguration()->getTorExitPaths());
    }
}