<?php

namespace Waca\Router;

use Exception;
use Waca\Tasks\IRoutedTask;

/**
 * Interface IRequestRouter
 *
 * @package Waca\Router
 */
interface IRequestRouter
{
	/**
	 * @return IRoutedTask
	 * @throws Exception
	 */
	public function route();
}