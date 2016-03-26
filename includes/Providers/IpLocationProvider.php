<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Providers;

use Exception;
use SimpleXMLElement;
use Waca\DataObjects\GeoLocation;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\ILocationProvider;

/**
 * IP location provider
 */
class IpLocationProvider implements ILocationProvider
{
	/** @var string	 */
	private $apiKey;
	/** @var PdoDatabase  */
	private $database;

	/**
	 * IpLocationProvider constructor.
	 *
	 * @param PdoDatabase $database
	 * @param     string  $apiKey
	 */
	public function __construct(PdoDatabase $database, $apiKey)
	{
		$this->database = $database;
		$this->apiKey = $apiKey;
	}

	/**
	 * @param string $address
	 *
	 * @return array|null
	 * @throws Exception
	 * @throws OptimisticLockFailedException
	 */
	public function getIpLocation($address)
	{
		$address = trim($address);

		// lets look in our database first.
		$location = GeoLocation::getByAddress($address, $this->database);

		if ($location != null) {
			// touch cache timer
			$location->save();

			return $location->getData();
		}

		// OK, it's not there, let's do an IP2Location lookup.
		$result = $this->getResult($address);

		if ($result != null) {
			$location = new GeoLocation();
			$location->setDatabase($this->database);
			$location->setAddress($address);
			$location->setData($result);
			$location->save();

			return $result;
		}

		return null;
	}

	// adapted from http://www.ipinfodb.com/ip_location_api.php

	/**
	 * @param string $ip
	 *
	 * @return array|null
	 */
	private function getResult($ip)
	{
		try {
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
				$xml = @file_get_contents($this->getApiBase() . '?key=' . $this->apiKey . '&ip=' . $ip . '&format=xml');

				$response = @new SimpleXMLElement($xml);

				$result = array();

				foreach ($response as $field => $value) {
					$result[(string)$field] = (string)$value;
				}

				return $result;
			}
		}
		catch (Exception $ex) {
			return null;

			// TODO: do something smart here, or wherever we use this value.
			// This is just a temp hack to squash errors on the UI for now.
		}

		return null;
	}

	/**
	 * @return string
	 */
	protected function getApiBase()
	{
		return "http://api.ipinfodb.com/v3/ip-city/";
	}
}
