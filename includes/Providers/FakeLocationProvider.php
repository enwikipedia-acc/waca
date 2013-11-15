<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

class FakeLocationProvider implements ILocationProvider
{
    public function __construct(PdoDatabase $database, $apikey)
    {
        // do nothing.
    }
    
    public function getIpLocation($address)
    {
        return null;
    }
}
