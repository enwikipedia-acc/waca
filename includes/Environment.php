<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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
