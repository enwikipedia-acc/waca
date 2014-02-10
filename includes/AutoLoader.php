<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

/**
 * AutoLoader short summary.
 *
 * AutoLoader description.
 *
 * @version 1.0
 * @author stwalkerster
 */
class AutoLoader
{
    public static function load($class)
    {
        global $filepath;
        
        $paths = array(
            $filepath . $class . ".php",
            $filepath . 'includes/' . $class . ".php",
            $filepath . 'includes/DataObjects/' . $class . ".php",
            $filepath . 'includes/Providers/' . $class . ".php",
            $filepath . 'includes/Providers/Interfaces/' . $class . ".php",
        );
        
        foreach($paths as $file)
        {
            if(file_exists($file))
            {
                require_once($file);
            }
        }
    }
}
