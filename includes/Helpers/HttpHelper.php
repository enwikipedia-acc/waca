<?php

namespace Waca\Helpers;

use Waca\Exceptions\CurlException;
use Waca\SiteConfiguration;

class HttpHelper
{
	private $curlHandle;

	/**
	 * HttpHelper constructor.
	 *
	 * @param SiteConfiguration $configuration
	 */
	public function __construct(SiteConfiguration $configuration)
	{
		$this->curlHandle = curl_init();

		curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curlHandle, CURLOPT_USERAGENT, $configuration->getUserAgent());

		if ($configuration->getCurlDisableVerifyPeer()) {
			curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, false);
		}
	}

	public function __destruct()
	{
		curl_close($this->curlHandle);
	}

	/**
	 * Fetches the content of a URL, with an optional parameter set.
	 *
	 * @todo Probably need to make this much, much better.
	 *
	 * @param string     $url        The URL to fetch.
	 * @param null|array $parameters Key/value pair of GET parameters to add to the request.
	 *                               Null lets you handle it yourself.
	 *
	 * @return string
	 * @throws CurlException
	 */
	public function get($url, $parameters = null)
	{
		if ($parameters !== null && is_array($parameters)) {
			$getString = $this->createGetString($parameters);
			$url .= $getString;
		}

		curl_setopt($this->curlHandle, CURLOPT_URL, $url);
		$result = curl_exec($this->curlHandle);

		if ($result === false) {
			$error = curl_error($this->curlHandle);
			throw new CurlException('Remote request failed with error ' . $error);
		}

		return $result;
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 * @category Security-Critical
	 */
	public static function createGetString($parameters)
	{
		$getData = array();

		foreach ($parameters as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $v) {
					$getData[] = $key . '[]=' . urlencode($v);
				}
			}
			else {
				if ($value === true) {
					$getData[] = $key;
				}
				else {
					$getData[] = $key . '=' . urlencode($value);
				}
			}
		}
		$getString = '?' . implode('&', $getData);

		return $getString;
	}
}