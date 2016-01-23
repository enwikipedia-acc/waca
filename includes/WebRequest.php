<?php

namespace Waca;

use Waca\Providers\Interfaces\IGlobalStateProvider;

/**
 * Holds helper functions regarding the current request.
 *
 * This is the only place where it is allowed to use super-globals, but even then access MUST be pushed through the
 * global state provider to allow for unit tests. It's strongly recommended to do sanitising of data here, especially
 * if extra logic is required to get a deterministic value, like isHttps().
 *
 * @package Waca
 */
class WebRequest
{
	/**
	 * @var IGlobalStateProvider Provides access to the global state.
	 */
	private static $globalStateProvider;

	/**
	 * Returns a boolean value if the request was submitted with the HTTP POST method.
	 * @return bool
	 */
	public static function wasPosted()
	{
		$server = &self::$globalStateProvider->getServerSuperGlobal();

		if(isset($server["REQUEST_METHOD"]) && $server["REQUEST_METHOD"] == "POST")
		{
			return true;
		}

		return false;
	}

	/**
	 * Gets a boolean value stating whether the request was served over HTTPS or not.
	 * @return bool
	 */
	public static function isHttps()
	{
		$server = &self::$globalStateProvider->getServerSuperGlobal();

		if (isset($server['HTTP_X_FORWARDED_PROTO'])) {
			if ($server['HTTP_X_FORWARDED_PROTO'] === 'https') {
				// Client <=> Proxy is encrypted
				return true;
			}
			else {
				// Proxy <=> Server link unknown, Client <=> Proxy is not encrypted.
				return false;
			}
		}

		if (isset($server['HTTPS'])) {
			if ($server['HTTPS'] === 'off') {
				// ISAPI on IIS breaks the spec. :(
				return false;
			}

			if ($server['HTTPS'] !== '') {
				// Set to a non-empty value
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets the path info
	 *
	 * @return array Array of path info segments
	 */
	public static function pathInfo()
	{
		$server = &self::$globalStateProvider->getServerSuperGlobal();
		if (!isset($server['PATH_INFO'])) {
			return array();
		}

		return array_filter(explode('/', $server['PATH_INFO']));
	}

	public static function setGlobalStateProvider($globalState)
	{
		self::$globalStateProvider = $globalState;
	}
}