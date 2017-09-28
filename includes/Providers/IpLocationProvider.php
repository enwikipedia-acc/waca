<?php

/**
 * IP location provider
 */
class IpLocationProvider implements ILocationProvider
{
	private $apikey;
	private $database;

	public function __construct(PdoDatabase $database, $apikey)
	{
		$this->database = $database;
		$this->apikey = $apikey;
	}

	public function getIpLocation($address)
	{
		$address = trim($address);

		// lets look in our database first.
		$location = GeoLocation::getByAddress($address, $this->database, true);

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
	 * @return array|null
	 */
	private function getResult($ip)
	{
		try {
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
				$xml = @file_get_contents($this->getApiBase() . '?key=' . $this->apikey . '&ip=' . $ip . '&format=xml');

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

	protected function getApiBase()
	{
		return "http://api.ipinfodb.com/v3/ip-city/";
	}
}
