<?php

/**
 * IXffTrustProvider provides methods for determining IP trust
 *
 * IXffTrustProvider gives two methods - one for determining if an IP address is trusted to forward XFF headers
 * correctly, and another to parse the inbound requests to get the trusted IP address from the chain.
 *
 * @version 1.0
 * @author  stwalkerster
 */
interface IXffTrustProvider
{
	/**
	 * Returns a value if the IP address is a trusted proxy
	 *
	 * @param string      $ip
	 *
	 * @return bool
	 */
	public function isTrusted($ip);

	/**
	 * Gets the last trusted IP in the proxy chain.
	 *
	 * @param string $ip      The IP address from REMOTE_ADDR
	 * @param string $proxyIp The contents of the XFF header.
	 *
	 * @return string Trusted source IP address
	 */
	public function getTrustedClientIp($ip, $proxyIp);
}
