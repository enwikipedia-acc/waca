<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Providers\GlobalState;

/**
 * Class GlobalStateProvider
 *
 * DO NOT USE THIS CLASS.
 * (Unless your name is <something>Test or WebRequest).
 *
 * @package Waca\Providers
 */
class GlobalStateProvider implements IGlobalStateProvider
{
    /**
     * @return array
     */
    public function &getServerSuperGlobal()
    {
        return $_SERVER;
    }

    /**
     * @return array
     */
    public function &getGetSuperGlobal()
    {
        return $_GET;
    }

    /**
     * @return array
     */
    public function &getPostSuperGlobal()
    {
        return $_POST;
    }

    /**
     * @return array
     */
    public function &getSessionSuperGlobal()
    {
        return $_SESSION;
    }

    /**
     * @return array
     */
    public function &getCookieSuperGlobal()
    {
        return $_COOKIE;
    }
}