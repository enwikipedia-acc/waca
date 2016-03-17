<?php

namespace Waca\Security;

use DateTimeImmutable;

class Token
{
	/** @var string */
	private $tokenData;
	/** @var string */
	private $context;
	/** @var DateTimeImmutable */
	private $generationTimestamp;
	/** @var DateTimeImmutable */
	private $usageTimestamp;
	/** @var bool */
	private $used;

	/**
	 * Token constructor.
	 *
	 * @param string $tokenData
	 * @param string $context
	 */
	public function __construct($tokenData, $context)
	{
		$this->tokenData = $tokenData;
		$this->context = $context;
		$this->generationTimestamp = new DateTimeImmutable();
		$this->usageTimestamp = null;
		$this->used = false;
	}

	/**
	 * @return DateTimeImmutable
	 */
	public function getGenerationTimestamp()
	{
		return $this->generationTimestamp;
	}

	/**
	 * @return string
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * @return string
	 */
	public function getTokenData()
	{
		return $this->tokenData;
	}

	/**
	 * Returns a value indicating whether the token has already been used or not
	 *
	 * @return boolean
	 */
	public function isUsed()
	{
		return $this->used;
	}

	/**
	 * Marks the token as used
	 */
	public function markAsUsed()
	{
		$this->used = true;
		$this->usageTimestamp = new DateTimeImmutable();
	}

	/**
	 * @return DateTimeImmutable
	 */
	public function getUsageTimestamp()
	{
		return $this->usageTimestamp;
	}
}