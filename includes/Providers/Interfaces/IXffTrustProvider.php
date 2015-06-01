<?php

/**
 * IXffTrustProvider short summary.
 *
 * IXffTrustProvider description.
 *
 * @version 1.0
 * @author stwalkerster
 */
interface IXffTrustProvider
{
	/**
	 * Returns a value if the IP address is a trusted proxy
	 * @param string $ip
	 * @param PdoDatabase $database
	 * @return bool
	 */
	public function isTrusted($ip, PdoDatabase $database = null);
}
