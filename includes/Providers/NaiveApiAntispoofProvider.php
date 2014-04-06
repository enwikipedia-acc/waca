<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

class NaiveApiAntispoofProvider implements IAntiSpoofProvider
{
    public function getSpoofs($username)
    {
        global $mediawikiWebServiceEndpoint;
        
        // get the data from the API
        $data = file_get_contents( $mediawikiWebServiceEndpoint . "?action=antispoof&format=php&username=" . urlencode( $username ) );
        $result = unserialize($data);
        
        if( $result['antispoof']['result'] == "pass" )
        {
            // All good here!
            return array();
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
