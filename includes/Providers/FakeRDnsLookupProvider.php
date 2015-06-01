<?php

/**
 * Mock RDNS provider for testing and development
 */
class FakeRDnsLookupProvider implements IRDnsProvider
{
	public function __construct(PdoDatabase $database)
	{
	}

	public function getRdns($address)
	{
		return "fake.rdns.result.local";
	}
}
