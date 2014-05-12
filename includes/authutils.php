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
            // syntax: :0:HASH  OR   HASH
            // syntax: :1:SALT:HASH
            // syntax: :2:x:HASH
            
            // check the version is one of the allowed ones:
            if( $minimumPasswordVersion > $data[ 0 ] ) return false;
            
            // re-encrypt the new password
            if( $data[ 0 ] == 0 )
            {
                return $credentials == self::encryptVersion0($password, $data[ 1 ]); 
            }
            if ( $data[ 0 ] == 1 )   
            {
                return $credentials == self::encryptVersion1($password, $data[ 1 ]);  
            }
            if( $data[ 0 ] == 2 )
            {
                return self::verifyVersion2($password, $data[ 2 ]); 
            }
            
            return false;
        } 
        else 
        { 
            // old style, eew.
        
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