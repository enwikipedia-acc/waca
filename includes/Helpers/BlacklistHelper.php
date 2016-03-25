<?php

namespace Waca\Helpers;

use Waca\Exceptions\CurlException;
use Waca\Helpers\Interfaces\IBlacklistHelper;

class BlacklistHelper implements IBlacklistHelper
{
	/** @var HttpHelper */
	private $httpHelper;
	/**
	 * Cache of previously requested usernames
	 * @var array
	 */
	private $cache = array();
	/** @var string */
	private $mediawikiWebServiceEndpoint;

	/**
	 * BlacklistHelper constructor.
	 *
	 * @param HttpHelper $httpHelper
	 * @param string     $mediawikiWebServiceEndpoint
	 */
	public function __construct(HttpHelper $httpHelper, $mediawikiWebServiceEndpoint)
	{
		$this->httpHelper = $httpHelper;
		$this->mediawikiWebServiceEndpoint = $mediawikiWebServiceEndpoint;
	}

	/**
	 * Returns a value indicating whether the provided username is blacklisted by the on-wiki title blacklist
	 *
	 * @param string $username
	 *
	 * @return false|string False if the username is not blacklisted, else the blacklist entry.
	 */
	public function isBlacklisted($username)
	{
		if (isset($this->cache[$username])) {
			$result = $this->cache[$username];
			if ($result === false) {
				return false;
			}

			return $result['line'];
		}

		$result = $this->performWikiLookup($username);

		if($result['result'] === 'ok')
		{
			// not blacklisted
			$this->cache[$username] = false;
			return false;
		}
		else{
			$this->cache[$username] = $result;
			return $result['line'];
		}
	}

	/**
	 * Performs a fetch to MediaWiki for the relevant title blacklist entry
	 *
	 * @param string $username The username to look up
	 *
	 * @return array
	 * @throws CurlException
	 */
	private function performWikiLookup($username)
	{
		$endpoint = $this->mediawikiWebServiceEndpoint;

		$parameters = array(
			'action'       => 'titleblacklist',
			'format'       => 'php',
			'tbtitle'      => $username,
			'tbaction'     => 'new-account',
			'tbnooverride' => true,
		);

		$apiResult = $this->httpHelper->get($endpoint, $parameters);

		$data = unserialize($apiResult);
		return $data['titleblacklist'];
	}
}