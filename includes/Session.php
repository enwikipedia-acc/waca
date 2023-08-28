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
 * Class Session
 *
 * This class handles the low-level starting and destroying of sessions.
 *
 * @package Waca
 */
class Session
{
    public static function start()
    {
        ini_set('session.cookie_httponly', 1);

        if (WebRequest::isHttps()) {
            ini_set('session.cookie_secure', 1);
        }

        session_start();
    }

    public static function destroy()
    {
        session_destroy();
    }

    public static function restart()
    {
        self::destroy();
        self::start();
    }
}
