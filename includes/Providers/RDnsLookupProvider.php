<?php

/**
 * Basic RDNS loopup provider.
 */
class RDnsLookupProvider implements IRDnsProvider
{
	public function __construct(PdoDatabase $database)
	{
	}

	public function getRdns($address)
	{
		$address = trim($address);

		// OK, it's not there, let's do an rdns lookup.
		$result = @ gethostbyaddr($address);

		if ($result !== false) {
			return $result;
		}

		return null;
	}
}
