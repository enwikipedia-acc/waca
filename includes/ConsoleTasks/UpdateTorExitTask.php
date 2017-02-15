<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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