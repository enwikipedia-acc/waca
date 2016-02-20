<?php

namespace Waca;

use Exception;
use Waca\Pages\Page404;
use Waca\Pages\PageMain;

interface IRequestRouter
{
	/**
	 * @return PageBase
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