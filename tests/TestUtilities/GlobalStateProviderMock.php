<?php

namespace Waca\Tests\TestUtilities;

use Waca\Providers\Interfaces\IGlobalStateProvider;

/**
 * Class GlobalStateProviderMock
 * @package Waca\Tests\TestUtilities
 */
class GlobalStateProviderMock implements IGlobalStateProvider
{
	/**
	 * @var array
	 */
	private $server;
	/**
	 * @var array
	 */
	private $get;
	/**
	 * @var array
	 */
	private $post;
	/**
	 * @var array
	 */
	private $session;

	/**
	 * @param $server
	 */
	public function setServerSuperGlobal($server)
	{
		$this->server = $server;
	}

	/**
	 * @param $get
	 */
	public function setGetSuperGlobal($get)
	{
		$this->get = $get;
	}

	/**
	 * @param $post
	 */
	public function setPostSuperGlobal($post)
	{
		$this->post = $post;
	}

	/**
	 * @param $session
	 */
	public function setSessionSuperGlobal($session)
	{
		$this->session = $session;
	}

	/**
	 * @return array
	 */
	public function &getServerSuperGlobal()
	{
		return $this->server;
	}

	/**
	 * @return array
	 */
	public function &getGetSuperGlobal()
	{
		return $this->get;
	}

	/**
	 * @return array
	 */
	public function &getPostSuperGlobal()
	{
		return $this->post;
	}

	/**
	 * @return array
	 */
	public function &getSessionSuperGlobal()
	{
		return $this->session;
	}
}