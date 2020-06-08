<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tasks;

use Waca\API\IApiAction;

abstract class ApiPageBase extends TaskBase implements IRoutedTask, IApiAction
{
    final public function execute()
    {
        $this->main();
    }

    /**
     * @param string $routeName
     */
    public function setRoute($routeName)
    {
        // no-op
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return 'main';
    }
}
