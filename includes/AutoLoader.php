<?php

/**
 * AutoLoader for the new classes
 */
class AutoLoader
{
    public static function load($class)
    {
        global $filepath;
        
        // handle namespaces sensibly
        if(strpos($class, "Waca") !== false)
        {
            $class = str_replace("Waca\\", "", $class);
        }
        
        $paths = array(
            $filepath . $class . ".php",
            $filepath . 'includes/' . $class . ".php",
            $filepath . 'includes/DataObjects/' . $class . ".php",
            $filepath . 'includes/Providers/' . $class . ".php",
            $filepath . 'includes/Providers/Interfaces/' . $class . ".php",
        );
        
        // extra includes which are awkward to autoload
        require_once($filepath . 'oauth/OAuthUtility.php');
        require_once($filepath . 'lib/mediawiki-extensions-OAuth/lib/OAuth.php');
        require_once($filepath . 'lib/mediawiki-extensions-OAuth/lib/JWT.php');
        
        foreach($paths as $file)
        {
            if(file_exists($file))
            {
                require_once($file);
            }
        }
    }
}
