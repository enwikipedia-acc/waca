<?php

/**
 * XffTrustProvider short summary.
 *
 * XffTrustProvider description.
 *
 * @version 1.0
 * @author stwalkerster
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

	/**
	 * Creates a new instance of the trust provider
	 * @param string[] $squidIpList List of IP addresses to pre-approve
	 */
	public function __construct($squidIpList)
	{
		$this->trustedCache = $squidIpList;
	}

	/**
	 * Returns a value if the IP address is a trusted proxy
	 * @param string $ip
	 * @param PdoDatabase $database
	 * @return bool
	 */
	public function isTrusted($ip, PdoDatabase $database = null)
	{
		if (in_array($ip, $this->trustedCache)) {
			return true;
		}

		if (in_array($ip, $this->untrustedCache)) {
			return false;
		}

		if ($database == null) {
			$database = gGetDb();
		}

		$query = "SELECT COUNT(*) FROM xfftrustcache WHERE ip = :ip;";
		$statement = $database->prepare($query);
		$statement->execute(array(":ip" => $ip));
		$result = $statement->fetchColumn();
		$statement->closeCursor();

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
}
