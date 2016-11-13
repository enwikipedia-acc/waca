<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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
}