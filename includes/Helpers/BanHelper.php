<?php

namespace Waca\Helpers;

use Ban;
use IBanHelper;
use PdoDatabase;

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
