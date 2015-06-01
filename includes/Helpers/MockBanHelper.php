<?php

class MockBanHelper implements IBanHelper
{
	/**
	 * Summary of $result
	 * @var Ban|boolean
	 */
	private $result = false;

	/**
	 * Summary of setResult
	 * @param Ban|boolean $result 
	 */
	public function setResult($result)
	{
		$this->result = $result;
	}

	/**
	 * Summary of nameIsBanned
	 * @param string $name The name to test if is banned.
	 * @return Ban|boolean
	 */
	public function nameIsBanned($name)
	{
		return $this->result;
	}

	/**
	 * Summary of emailIsBanned
	 * @param string $email
	 * @return Ban|boolean
	 */
	public function emailIsBanned($email)
	{
		return $this->result;
	}

	/**
	 * Summary of ipIsBanned
	 * @param string $ip
	 * @return Ban|boolean
	 */
	public function ipIsBanned($ip)
	{
		return $this->result;
	}
}
