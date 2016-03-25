<?php

namespace Waca\Helpers\Interfaces;

interface IBlacklistHelper
{
	/**
	 * Returns a value indicating whether the provided username is blacklisted by the on-wiki title blacklist
	 *
	 * @param string $username
	 *
	 * @return bool
	 */
	public function isBlacklisted($username);
}