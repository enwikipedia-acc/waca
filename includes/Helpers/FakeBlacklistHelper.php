<?php

namespace Waca\Helpers;

use Waca\Helpers\Interfaces\IBlacklistHelper;

class FakeBlacklistHelper implements IBlacklistHelper
{
	/**
	 * Returns a value indicating whether the provided username is blacklisted by the on-wiki title blacklist
	 *
	 * @param string $username
	 *
	 * @return bool
	 */
	public function isBlacklisted($username)
	{
		// Short-circuit
		return false;
	}
}