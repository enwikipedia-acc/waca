<?php
namespace Waca\Providers\Interfaces;

/**
 * Interface IGlobalStateProvider
 * @package Waca\Providers\Interfaces
 */
interface IGlobalStateProvider
{
	/**
	 * @return array
	 */
	public function getServerSuperGlobal();

	/**
	 * @return array
	 */
	public function getGetSuperGlobal();

	/**
	 * @return array
	 */
	public function getPostSuperGlobal();

	/**
	 * @return array
	 */
	public function getSessionSuperGlobal();
}