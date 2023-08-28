<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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
