<?php

namespace Waca\Tasks;

use Exception;

interface IRoutedTask extends ITask
{
	/**
	 * Sets the route the request will take. Only should be called from the request router.
	 *
	 * @param $routeName string
	 *
	 * @throws Exception
	 * @category Security-Critical
	 */
	public function setRoute($routeName);

	/**
	 * Gets the name of the route that has been passed from the request router.
	 * @return string
	 */
	public function getRouteName();
}