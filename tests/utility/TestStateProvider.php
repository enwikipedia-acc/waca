<?php

namespace Waca\Tests\Utility;

use Waca\Providers\GlobalStateProvider;
use Waca\Providers\Interfaces\IGlobalStateProvider;

/**
 * Class TestStateProvider
 *
 * This class is only to be used for testing sets of the global state variables. For everything else, please use PHPUnit
 * mocks as normal.
 *
 * @package Waca\Tests\Utility
 */
class TestStateProvider extends GlobalStateProvider implements IGlobalStateProvider
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