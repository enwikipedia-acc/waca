<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

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