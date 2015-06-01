<?php

namespace Waca\API;

/**
 * API
 */
class Api
{
	public function __construct($httpOrigin)
	{
		header("Content-Type: text/xml");

		// javascript access control
		if ($httpOrigin !== null) {
			global $CORSallowed;

			if (in_array($httpOrigin, $CORSallowed)) {
				header("Access-Control-Allow-Origin: " . $httpOrigin);
			}
		}
	}

	public function execute($requestAction)
	{
		switch ($requestAction) {
			case "count":
				$result = new Actions\CountAction();
				$data = $result->run();
				break;
			case "status":
				$result = new Actions\StatusAction();
				$data = $result->run();
				break;
			case "stats":
				$result = new Actions\StatsAction();
				$data = $result->run();
				break;
			case "help":
				$result = new Actions\HelpAction();
				$data = $result->run();
				break;
			case "monitor":
				$result = new Actions\MonitorAction();
				$data = $result->run();
				break;
			default:
				$result = new Actions\UnknownAction();
				$data = $result->run();
				break;
		}

		return $data;
	}

	public static function getActionList()
	{
		return array("count", "status", "stats", "help", "monitor");
	}
}
