<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Providers;

use Exception;
use Waca\DataObjects\AntiSpoofCache;
use Waca\DataObjects\Domain;
use Waca\Helpers\HttpHelper;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\IAntiSpoofProvider;

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
     * @var HttpHelper
     */
    private $httpHelper;

    public function __construct(PdoDatabase $database, HttpHelper $httpHelper)
    {
        $this->database = $database;
        $this->httpHelper = $httpHelper;
    }

    public function getSpoofs($username)
    {
        // FIXME: domains!
        /** @var Domain $domain */
        $domain = Domain::getById(1, $this->database);

        /** @var AntiSpoofCache $cacheResult */
        $cacheResult = AntiSpoofCache::getByUsername($username, $this->database);
        if ($cacheResult == false) {
            // get the data from the API
            $data = $this->httpHelper->get($domain->getWikiApiPath(), array(
                'action'   => 'antispoof',
                'format'   => 'php',
                'username' => $username,
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
