<?php

/**
 * Cached RDNS Lookup Provider
 * 
 * Provides a service to look up the reverse DNS of an IP address, and caches
 * the result in the database.
 */
class CachedRDnsLookupProvider implements IRDnsProvider
{
    private $database;
    
    public function __construct(PdoDatabase $database)
    {
        $this->database = $database;
    }
    
    public function getRdns($address)
    {
        $address = trim($address);
        
        // lets look in our cache database first.
        $rdns = RDnsCache::getByAddress($address, $this->database);
        
        if($rdns != null)
        {
            // touch cache timer
            $rdns->save();
            
            return $rdns->getData();   
        }
        
        // OK, it's not there, let's do an rdns lookup.
        $result = @ gethostbyaddr($address);
        
        if($result !== false)
        {
            $rdns = new RDnsCache();
            $rdns->setDatabase($this->database);
            $rdns->setAddress($address);
            $rdns->setData($result);
            $rdns->save();
            
            return $result;
        }
        
        return null;
    }
}
