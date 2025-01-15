<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Router;

use Exception;
use Waca\API\Actions\CountAction;
use Waca\API\Actions\HelpAction;
use Waca\API\Actions\JsTemplateConfirmsAction;
use Waca\API\Actions\JsUsersAction;
use Waca\API\Actions\MetricsAction;
use Waca\API\Actions\MonitorAction;
use Waca\API\Actions\StatsAction;
use Waca\API\Actions\StatusAction;
use Waca\API\Actions\UnknownAction;
use Waca\Tasks\IRoutedTask;
use Waca\WebRequest;

class ApiRequestRouter implements IRequestRouter
{
    /**
     * @return string[]
     */
    public static function getActionList()
    {
        return array('count', 'status', 'stats', 'help', 'monitor', 'metrics');
    }

    /**
     * @return IRoutedTask
     * @throws Exception
     */
    public function route()
    {
        $requestAction = WebRequest::getString('action');

        switch ($requestAction) {
            case "count":
                $result = new CountAction();
                break;
            case "status":
                $result = new StatusAction();
                break;
            case "stats":
                $result = new StatsAction();
                break;
            case "help":
                $result = new HelpAction();
                break;
            case "monitor":
                $result = new MonitorAction();
                break;
            case "users":
                $result = new JsUsersAction();
                break;
            case "templates":
                $result = new JsTemplateConfirmsAction();
                break;
            case 'metrics':
                $result = new MetricsAction();
                break;
            default:
                $result = new UnknownAction();
                break;
        }

        return $result;
    }
}
