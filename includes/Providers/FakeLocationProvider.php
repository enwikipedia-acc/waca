<?php

/**
 * Mock IP Location provider for testing and development.
 */
class FakeLocationProvider implements ILocationProvider
{
	public function __construct(PdoDatabase $database, $apikey)
	{
		// do nothing.
	}

	public function getIpLocation($address)
	{
		return null;
	}
}
