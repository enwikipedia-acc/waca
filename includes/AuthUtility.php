<?php

require_once 'lib/password_compat/lib/password.php';

class AuthUtility
{
    /**
     * Test the specified data against the specified credentials
     * @param string $credentials
     * @return boolean
     */
    public static function testCredentials( $password, $credentials )
    {
        global $minimumPasswordVersion;

        if(substr($credentials, 0, 1) != ":") {
            return false;
        }

        // determine password version
        $data = explode(':', substr($credentials, 1));

        // call the encryptVersion function for the version that this password actually is.
        // syntax: :1:SALT:HASH
        // syntax: :2:x:HASH

        // check the version is one of the allowed ones:
        if($minimumPasswordVersion > $data[ 0 ]) {
            return false;
        }

        if($data[ 0 ] == 1) {
            return $credentials == self::encryptVersion1($password, $data[ 1 ]);
        }

        if($data[ 0 ] == 2) {
            return self::verifyVersion2($password, $data[ 2 ]);
        }

        return false;
    }

    /**
     * @param string $credentials
     */
    public static function isCredentialVersionLatest($credentials)
    {
        return substr($credentials, 0, 3) === ":2:";
    }

    public static function encryptPassword($password)
    {
        return self::encryptVersion2($password);
    }

    private static function encryptVersion1( $password, $salt )
    {
        return ':1:' . $salt . ':' . md5($salt . '-' . md5($password));
    }

    private static function encryptVersion2($password)
    {
        return ':2:x:' . password_hash($password, PASSWORD_BCRYPT);
    }

    private static function verifyVersion2($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
