<?php
use Waca\Helpers\HttpHelper;

/**
 * Cached API Antispoof Provider
 *
 * Provides a list of similar usernames from a MediaWiki API module, and caches
 * it in the database.
 */
class CachedApiAntispoofProvider implements IAntiSpoofProvider
{
	/**
	 * @var PdoDatabase
	 */
	private $database;
	/**
	 * @var string
	 */
	private $mediawikiWebServiceEndpoint;
	/**
	 * @var HttpHelper
	 */
	private $httpHelper;

	public function __construct(PdoDatabase $database, $mediawikiWebServiceEndpoint, HttpHelper $httpHelper)
	{
		$this->database = $database;
		$this->mediawikiWebServiceEndpoint = $mediawikiWebServiceEndpoint;
		$this->httpHelper = $httpHelper;
	}

	public function getSpoofs($username)
	{
		$cacheResult = AntiSpoofCache::getByUsername($username, $this->database);
		if ($cacheResult == false) {
			// get the data from the API
			$data = $this->httpHelper->get($this->mediawikiWebServiceEndpoint, array(
				'action' => 'antispoof',
				'format' => 'php',
				'username' => $username
			));

			$cacheEntry = new AntiSpoofCache();
			$cacheEntry->setDatabase($this->database);
			$cacheEntry->setUsername($username);
			$cacheEntry->setData($data);
			$cacheEntry->save();

			$cacheResult = $cacheEntry;
		}
		else {
			$data = $cacheResult->getData();
		}

		$result = unserialize($data);

		if (!isset($result['antispoof']) || !isset($result['antispoof']['result'])) {
			$cacheResult->delete();

			if (isset($result['error']['info'])) {
				throw new Exception("Unrecognised API response to query: " . $result['error']['info']);
			}

			throw new Exception("Unrecognised API response to query.");
		}

		if ($result['antispoof']['result'] == "pass") {
			// All good here!
			return array();
		}

		if ($result['antispoof']['result'] == "conflict") {
			// we've got conflicts, let's do something with them.
			return $result['antispoof']['users'];
		}

		if ($result['antispoof']['result'] == "error") {
			// we've got conflicts, let's do something with them.
			throw new Exception("Encountered error while getting result: " . $result['antispoof']['error']);
		}

		throw new Exception("Unrecognised API response to query.");
	}
}
