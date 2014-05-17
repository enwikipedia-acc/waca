<?php

if (!defined("ACC")) {
    die();
} // Invalid entry point


class Environment
{
    private static $toolVersion;
    
    /**
     * Gets the tool version, using cached data if available.
     * @return mixed
     */
    public static function getToolVersion()
    {
        if(self::$toolVersion == false)
        {
            self::$toolVersion = exec("git describe --always --dirty");
        }
        
        return self::$toolVersion;   
    }
}
