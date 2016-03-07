<?php

namespace Waca\Providers;

use PDOStatement;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\IXffTrustProvider;

/**
 * XffTrustProvider short summary.
 *
 * XffTrustProvider description.
 *
 * @version 1.0
 * @author  stwalkerster
 */
class XffTrustProvider implements IXffTrustProvider
{
	/**
	 * Array of IP addresses which are TRUSTED proxies
	 * @var string[]
	 */
	private $trustedCache = array();
	/**
	 * Array of IP addresses which are UNTRUSTED proxies
	 * @var string[]
	 */
	private $untrustedCache = array();
	/** @var PDOStatement */
	private $trustedQuery;
	/**
	 * @var PdoDatabase
	 */
	private $database;

	/**
	 * Creates a new instance of the trust provider
	 *
	 * @param string[]    $squidIpList List of IP addresses to pre-approve
	 * @param PdoDatabase $database
	 */
	public function __construct($squidIpList, PdoDatabase $database)
	{
		$this->trustedCache = $squidIpList;
		$this->database = $database;
	}

	/**
	 * Returns a value if the IP address is a trusted proxy
	 *
	 * @param string $ip
	 *
	 * @return bool
	 */
	public function isTrusted($ip)
	{
		if (in_array($ip, $this->trustedCache)) {
			return true;
		}

		if (in_array($ip, $this->untrustedCache)) {
			return false;
		}

		if ($this->trustedQuery === null) {
			$query = "SELECT COUNT(id) FROM xfftrustcache WHERE ip = :ip;";
			$this->trustedQuery = $this->database->prepare($query);
		}

		$this->trustedQuery->execute(array(":ip" => $ip));
		$result = $this->trustedQuery->fetchColumn();
		$this->trustedQuery->closeCursor();

		if ($result == 0) {
			$this->untrustedCache[] = $ip;

			return false;
		}

		if ($result >= 1) {
			$this->trustedCache[] = $ip;

			return true;
		}

		// something weird has happened if we've got here.
		// default to untrusted.
		return false;
	}

	/**
	 * Gets the last trusted IP in the proxy chain.
	 *
	 * @param string $ip      The IP address from REMOTE_ADDR
	 * @param string $proxyIp The contents of the XFF header.
	 *
	 * @return string Trusted source IP address
	 */
	public function getTrustedClientIp($ip, $proxyIp)
	{
		$clientIpAddress = $ip;
		if ($proxyIp) {
			$ipList = explode(",", $proxyIp);
			$ipList[] = $clientIpAddress;
			$ipList = array_reverse($ipList);

			foreach ($ipList as $ipNumber => $ipAddress) {
				if ($this->isTrusted(trim($ipAddress)) && $ipNumber < (count($ipList) - 1)) {
					continue;
				}

				$clientIpAddress = $ipAddress;
				break;
			}
		}

		return $clientIpAddress;
	}

	/**
	 * Takes an array( "low" => "high" ) values, and returns true if $needle is in at least one of them.
	 *
	 * @param array  $haystack
	 * @param string $ip
	 *
	 * @return bool
	 */
	public function ipInRange($haystack, $ip)
	{
		$needle = ip2long($ip);

		foreach ($haystack as $low => $high) {
			if (ip2long($low) <= $needle && ip2long($high) >= $needle) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Explodes a CIDR range into an array of addresses
	 *
	 * @param string $range A CIDR-format range
	 *
	 * @return array An array containing every IP address in the range
	 */
	public function explodeCidr($range)
	{
		$cidrData = explode('/', $range);

		if (!isset($cidrData[1])) {
			return array($range);
		}

		$blow = (
			str_pad(decbin(ip2long($cidrData[0])), 32, "0", STR_PAD_LEFT) &
			str_pad(str_pad("", $cidrData[1], "1"), 32, "0")
		);
		$bhigh = ($blow | str_pad(str_pad("", $cidrData[1], "0"), 32, "1"));

		$list = array();

		$bindecBHigh = bindec($bhigh);
		for ($x = bindec($blow); $x <= $bindecBHigh; $x++) {
			$list[] = long2ip($x);
		}

		return $list;
	}
}
