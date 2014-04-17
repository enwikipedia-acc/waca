<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

class CachedApiAntispoofProvider implements IAntiSpoofProvider
{
    public function getSpoofs($username)
    {
        global $mediawikiWebServiceEndpoint;
        
        $cacheResult = AntiSpoofCache::getByUsername($username, gGetDb());
        if($cacheResult == false)
        {
            // get the data from the API
            $data = file_get_contents( $mediawikiWebServiceEndpoint . "?action=antispoof&format=php&username=" . urlencode( $username ) );
                
            $cacheEntry = new AntiSpoofCache();
            $cacheEntry->setDatabase(gGetDb());
            $cacheEntry->setUsername($username);
            $cacheEntry->setData($data);
            $cacheEntry->save();
        }
        else
        {
            $data = $cacheResult->getData();   
        }
        
        $result = unserialize($data);
        
        if( $result['antispoof']['result'] == "pass" )
        {
            // All good here!
            return false;
        }
        
        if( $result['antispoof']['result'] == "conflict" )
        {
            // we've got conflicts, let's do something with them.
            return $result['antispoof']['users'];
        }
        
        if( $result['antispoof']['result'] == "error" )
        {
            // we've got conflicts, let's do something with them.
            throw new Exception( "Encountered error while getting result: " . $result['antispoof']['error'] );
        }
        
        throw new Exception( "Unrecognised API response to query." );
    }
}
