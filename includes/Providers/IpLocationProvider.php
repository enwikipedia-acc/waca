<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

interface IIpLocationProvider
{
    public function getIpLocation($address);   
}

class IpLocationProvider implements IIpLocationProvider
{
    private $apikey;
    private $database;
    
    public function __construct(PdoDatabase $database, string $apikey)
    {
        $this->database = $database;
        $this->apikey = $apikey;
    }
    
    public function getIpLocation($address)
    {
        // lets look in our database first.
        $location = GeoLocation::getByAddress($address);
        
        if($location != null)
        {
            // touch cache timer
            $location->save();
            
            return $location->getData();   
        }
        
        // OK, it's not there, let's do an IP2Location lookup.
        $result = $this->getResult($address);
        
        if($result != null)
        {
            $location = new GeoLocation();
            $location->setDatabase($this->database);
            $location->setAddress($address);
            $location->setData($result);
            $location->save();
            
            return $result;
        }
    }
    
    // adapted from http://www.ipinfodb.com/ip_location_api.php
	private function getResult($ip)
    {
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
        {
			$xml = @file_get_contents('http://api.ipinfodb.com/v3/ip-city/?key=' . $this->apikey . '&ip=' . $ip . '&format=xml');

			if(get_magic_quotes_runtime())
            {
				$xml = stripslashes($xml);
			}

			$response = @new SimpleXMLElement($xml);

			foreach($response as $field=>$value)
            {
				$result[(string)$field] = (string)$value;
			}

			return $result;
		}
        
		return null;
	}
}
