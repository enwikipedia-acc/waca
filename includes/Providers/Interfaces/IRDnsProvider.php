<?php

namespace Waca\Providers\Interfaces;

/**
 * Reverse DNS provider interface
 */
interface IRDnsProvider
{
	/**
	 * Gets the reverse DNS address for an IP
	 *
	 * @param string $address
	 *
	 * @return string
	 */
	public function getRdns($address);
}
