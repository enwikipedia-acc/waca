<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

class FakeRDnsLookupProvider implements IRDnsProvider
{
    public function __construct(PdoDatabase $database)
    {
    }
    
    public function getRdns($address)
    {
        return "fake.rdns.result.local";
    }
}
