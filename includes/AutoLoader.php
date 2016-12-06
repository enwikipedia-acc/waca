<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

/**
 * AutoLoader for the new classes
 */
class AutoLoader
{
    public static function load($class)
    {
        // handle namespaces sensibly
        if (strpos($class, "Waca") !== false) {
            // strip off the initial namespace
            $class = str_replace("Waca\\", "", $class);

            // swap backslashes for forward slashes to map to directory names
            $class = str_replace("\\", "/", $class);
        }

        $paths = array(
            __DIR__ . '/' . $class . ".php",
            __DIR__ . '/DataObjects/' . $class . ".php",
            __DIR__ . '/Providers/' . $class . ".php",
            __DIR__ . '/Providers/Interfaces/' . $class . ".php",
            __DIR__ . '/Validation/' . $class . ".php",
            __DIR__ . '/Helpers/' . $class . ".php",
            __DIR__ . '/Helpers/Interfaces/' . $class . ".php",
            __DIR__ . '/' . $class . ".php",
        );

        foreach ($paths as $file) {
            if (file_exists($file)) {
                require_once($file);
            }

            if (class_exists($class)) {
                return;
            }
        }
    }
}
