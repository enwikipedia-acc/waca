<?php

/*
 * Auth utilities functions
 * @author Simon Walker
 * @licence PD
 */

// Get all the classes.
require_once 'config.inc.php';

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client

class authutils {
    
    /**
     * Test the specified data against the specified credentials
     */
    public static function testCredentials( $password, $credentials ) {
        if( substr( $credentials, 0, 1 ) == ":" ) {
            // new style, but what version?
            $data = explode( ':', substr( $credentials, 1 ) );
            
            // call the encryptVersion function for the version that this password actually is.
            // syntax: :VERSION:SALT:HASH
            
            // re-encrypt the new password
            $newcrypt = call_user_func( array( "authutils", "encryptVersion" . $data[ 0 ] ), $password, $data[ 1 ] );
            
            // compare encryptions
            return ( $newcrypt == $credentials );
            
        } else { // old style, eew.
            // trigger_error("Old user password in use, please change your password.", E_USER_WARNING);
            return self::encryptVersion0( $password, "" ) == $credentials;
        }
    }
    
    public static function isCredentialVersionLatest( $credentials ) {
        return substr( $credentials, 0, 3 ) === ":1:";
    }
    
    public static function encryptPassword( $password ) {
        return self::encryptVersion1( $password, microtime() );
    }
    
    private static function encryptVersion0( $password, $salt ) {
        return md5( $password );
    }
    
    private static function encryptVersion1( $password, $salt ) {
        return ':1:' . $salt . ':' . md5( $salt . '-' . md5( $password ) );
    }
}