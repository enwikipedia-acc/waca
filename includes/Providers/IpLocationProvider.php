<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

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
        $location = GeoLocation::getByAddress($address, $this->database);
        
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
        
        return null;
    }
    
    // adapted from http://www.ipinfodb.com/ip_location_api.php
	private function getResult($ip)
    {
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ))
        {
			$xml = @file_get_contents( $this->getApiBase() . '?key=' . $this->apikey . '&ip=' . $ip . '&format=xml');

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
    
    protected function getApiBase()
    {
        return "http://api.ipinfodb.com/v3/ip-city/";
    }
}
