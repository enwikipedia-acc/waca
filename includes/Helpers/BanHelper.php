<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use Waca\DataObjects\Ban;
use Waca\Helpers\Interfaces\IBanHelper;
use Waca\PdoDatabase;

class BanHelper implements IBanHelper
{
	/**
	 * @var PdoDatabase
	 */
	private $database;

	public function __construct(PdoDatabase $database)
	{
		$this->database = $database;
	}

	/**
	 * Summary of nameIsBanned
	 *
	 * @param string $name The name to test if is banned.
	 *
	 * @return Ban
	 */
	public function nameIsBanned($name)
	{
		return Ban::getBanByTarget($name, "Name", $this->database);
	}

	/**
	 * Summary of emailIsBanned
	 *
	 * @param string $email
	 *
	 * @return Ban
	 */
	public function emailIsBanned($email)
	{
		return Ban::getBanByTarget($email, "EMail", $this->database);
	}

	/**
	 * Summary of ipIsBanned
	 *
	 * @param string $ip
	 *
	 * @return Ban
	 */
	public function ipIsBanned($ip)
	{
		return Ban::getBanByTarget($ip, "IP", $this->database);
	}
}
