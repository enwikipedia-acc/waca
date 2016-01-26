<?php

namespace Waca;

use User;
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

		if (isset($server["REQUEST_METHOD"]) && $server["REQUEST_METHOD"] == "POST") {
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

		$exploded = explode('/', $server['PATH_INFO']);

		// filter out empty values, and reindex from zero. Notably, the first element is always zero, since it starts
		// with a /
		return array_values(array_filter($exploded));
	}

	/**
	 * Gets the remote address of the web request
	 * @return null|string
	 */
	public static function remoteAddress()
	{
		$server = &self::$globalStateProvider->getServerSuperGlobal();

		if (isset($server['REMOTE_ADDR'])) {
			return $server['REMOTE_ADDR'];
		}

		return null;
	}

	/**
	 * Gets the XFF header contents for the web request
	 * @return null|string
	 */
	public static function forwardedAddress()
	{
		$server = &self::$globalStateProvider->getServerSuperGlobal();

		if (isset($server['HTTP_X_FORWARDED_FOR'])) {
			return $server['HTTP_X_FORWARDED_FOR'];
		}

		return null;
	}

	/**
	 * Sets the global state provider.
	 *
	 * Almost guaranteed this is not the method you want in production code.
	 *
	 * @param IGlobalStateProvider $globalState
	 */
	public static function setGlobalStateProvider($globalState)
	{
		self::$globalStateProvider = $globalState;
	}

	#region POST variables

	/**
	 * @param $key string
	 * @return null|string
	 */
	public static function postString($key)
	{
		$post = &self::$globalStateProvider->getPostSuperGlobal();
		if (!array_key_exists($key, $post)) {
			return null;
		}

		if ($post[$key] === "") {
			return null;
		}

		return (string)$post[$key];
	}

	/**
	 * @param $key
	 * @return null|string
	 */
	public static function postEmail($key)
	{
		$post = &self::$globalStateProvider->getPostSuperGlobal();
		if (!array_key_exists($key, $post)) {
			return null;
		}

		$filteredValue = filter_var($post[$key], FILTER_SANITIZE_EMAIL);

		if ($filteredValue === false) {
			return null;
		}

		return (string)$filteredValue;
	}

	/**
	 * @param string $key
	 * @return int|null
	 */
	public static function postInt($key)
	{
		$post = &self::$globalStateProvider->getPostSuperGlobal();
		if (!array_key_exists($key, $post)) {
			return null;
		}

		$filteredValue = filter_var($post[$key], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

		if ($filteredValue === null) {
			return null;
		}

		return (int)$filteredValue;
	}

	public static function postBoolean($key)
	{
		$get = &self::$globalStateProvider->getPostSuperGlobal();
		if (!array_key_exists($key, $get)) {
			return false;
		}

		// presence of parameter only
		if ($get[$key] === "") {
			return true;
		}

		if (in_array($get[$key], array(false, 'no', 'off', 0), true)) {
			return false;
		}

		return true;
	}

	#endregion

	#region GET variables

	/**
	 * @param $key
	 * @return bool
	 */
	public static function getBoolean($key)
	{
		$get = &self::$globalStateProvider->getGetSuperGlobal();
		if (!array_key_exists($key, $get)) {
			return false;
		}

		// presence of parameter only
		if ($get[$key] === "") {
			return true;
		}

		if (in_array($get[$key], array(false, 'no', 'off', 0), true)) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $key
	 * @return int|null
	 */
	public static function getInt($key)
	{
		$get = &self::$globalStateProvider->getGetSuperGlobal();
		if (!array_key_exists($key, $get)) {
			return null;
		}

		$filteredValue = filter_var($get[$key], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

		if ($filteredValue === null) {
			return null;
		}

		return (int)$filteredValue;
	}

	public static function getString($key)
	{
		$get = &self::$globalStateProvider->getGetSuperGlobal();
		if (!array_key_exists($key, $get)) {
			return null;
		}

		if ($get[$key] === "") {
			return null;
		}

		return (string)$get[$key];
	}

	#endregion

	/**
	 * Sets the logged-in user to the specified user.
	 *
	 * @param User $user
	 */
	public static function setLoggedInUser(User $user)
	{
		$session = &self::$globalStateProvider->getSessionSuperGlobal();

		$session['userID'] = $user->getId();
	}

	public static function getServerName()
	{
		return self::$globalStateProvider->getServerSuperGlobal()['SERVER_NAME'];
	}

	public static function getRequestUri()
	{
		return self::$globalStateProvider->getServerSuperGlobal()['REQUEST_URI'];
	}
}