<?php

/*
 * Auth utilities functions
 * @author Simon Walker
 * @licence PD
 */

// Get all the classes.
require_once 'config.inc.php';
require_once 'lib/password_compat/lib/password.php';

if (!defined("ACC")) {
	die();
} // Invalid entry point

class authutils {
    
    /**
     * Test the specified data against the specified credentials
     */
    public static function testCredentials( $password, $credentials ) {
        global $minimumPasswordVersion;
    
        if( substr( $credentials, 0, 1 ) == ":" ) {
            // new style, but what version?
            $data = explode( ':', substr( $credentials, 1 ) );
            
            // call the encryptVersion function for the version that this password actually is.
            // syntax: :VERSION:SALT:HASH
            
            // check the version is one of the allowed ones:
            if( $minimumPasswordVersion > $data[ 0 ] ) return false;
            
            if( $data[ 0 ] < 2 ) {
                // re-encrypt the new password
                $newcrypt = call_user_func( array( "authutils", "encryptVersion" . $data[ 0 ] ), $password, $data[ 1 ] );
            
                // compare encryptions
                return ( $newcrypt == $credentials );
            }
            
            return call_user_func( array( "authutils", "verifyVersion" . $data[ 0 ] ), $password, $data[ 2 ] );

        } else { // old style, eew.
        
            // not allowed this version of password
            if( $minimumPasswordVersion > 0 ) return false;
        
            // various different ways of escaping this have been done in the past.
            // we have to test all to make sure it's gonna work, reducing security.
            return ( self::encryptVersion0( $password, "" ) == $credentials
                || self::encryptVersion0( sanitize( $password ), "" ) == $credentials
                || self::encryptVersion0( mysql_escape_string( $password ), "" ) == $credentials
                );
        }
    }
    
    public static function isCredentialVersionLatest( $credentials ) {
        return substr( $credentials, 0, 3 ) === ":2:";
    }
    
    public static function encryptPassword( $password ) {
        return self::encryptVersion2( $password, microtime() );
    }
    
    private static function encryptVersion0( $password, $salt ) {
        return md5( $password );
    }
    
    private static function encryptVersion1( $password, $salt ) {
        return ':1:' . $salt . ':' . md5( $salt . '-' . md5( $password ) );
    }
    
    private static function encryptVersion2( $password, $salt ) {
        return ':2:x:' . password_hash( $password, PASSWORD_BCRYPT );
    }
    
    private static function verifyVersion2( $password, $hash ) {
        return password_verify( $password, $hash );
    }
}