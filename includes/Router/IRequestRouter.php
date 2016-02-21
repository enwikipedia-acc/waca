<?php

namespace Waca\Router;

use Exception;
use Waca\Tasks\InternalPageBase;

/**
 * Interface IRequestRouter
 *
 * @package Waca\Router
 */
interface IRequestRouter
{
	/**
	 * @return InternalPageBase
	 * @throws Exception
	 */
	public function route();

	/**
	 * @param $pathInfo
	 *
	 * @return array
	 */
	public function getRouteFromPath($pathInfo);
}