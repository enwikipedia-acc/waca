<?php

/**
 * Reverse DNS provider interface
 */
interface IRDnsProvider
{
	public function getRdns($address);
}
