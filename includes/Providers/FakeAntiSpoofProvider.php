<?php

namespace Waca\Providers;

use Exception;
use Waca\Providers\Interfaces\IAntiSpoofProvider;

/**
 * Mock AntiSpoof provider for testing or development work.
 */
class FakeAntiSpoofProvider implements IAntiSpoofProvider
{
	public function getSpoofs($username)
	{
		throw new Exception("This function is currently disabled.");
	}
}
