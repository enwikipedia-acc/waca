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
 * Class TestStateProvider
 *
 * This class is only to be used for testing sets of the global state variables. For everything else, please use PHPUnit
 * mocks as normal.
 *
 * @package Waca\Tests\Utility
 */
class FakeGlobalStateProvider extends GlobalStateProvider implements IGlobalStateProvider
{
    var $server = array();
    var $get = array();
    var $post = array();
    var $session = array();
    var $cookie = array();

    public function &getServerSuperGlobal()
    {
        return $this->server;
    }

    public function &getGetSuperGlobal()
    {
        return $this->get;
    }

    public function &getPostSuperGlobal()
    {
        return $this->post;
    }

    public function &getSessionSuperGlobal()
    {
        return $this->session;
    }

    public function &getCookieSuperGlobal()
    {
        return $this->cookie;
    }
}