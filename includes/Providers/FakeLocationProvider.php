<?php

namespace Waca\Providers;

use Waca\Providers\Interfaces\ILocationProvider;

/**
 * Mock IP Location provider for testing and development.
 */
class FakeLocationProvider implements ILocationProvider
{
	public function getIpLocation($address)
	{
		return null;
	}
}
