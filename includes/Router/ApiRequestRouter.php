<?php

namespace Waca\Router;

use Exception;
use Waca\API\Actions\CountAction;
use Waca\API\Actions\HelpAction;
use Waca\API\Actions\MonitorAction;
use Waca\API\Actions\StatsAction;
use Waca\API\Actions\StatusAction;
use Waca\API\Actions\UnknownAction;
use Waca\Tasks\IRoutedTask;
use Waca\WebRequest;

class ApiRequestRouter implements IRequestRouter
{
	/**
	 * @return array
	 */
	public static function getActionList()
	{
		return array("count", "status", "stats", "help", "monitor");
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
			default:
				$result = new UnknownAction();
				break;
		}

		return $result;
	}
}