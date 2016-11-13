<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

/**
 * Class providing information about the tool's runtime environment
 */
class Environment
{
    /**
     * @var string Cached copy of the tool version
     */
    private static $toolVersion = null;

    /**
     * Gets the tool version, using cached data if available.
     * @return string
     */
    public static function getToolVersion()
    {
        if (self::$toolVersion === null) {
            self::$toolVersion = exec("git describe --always --dirty");
        }

        return self::$toolVersion;
    }
}
