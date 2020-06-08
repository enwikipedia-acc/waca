<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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
