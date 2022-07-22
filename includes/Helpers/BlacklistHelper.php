<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use Waca\DataObjects\Domain;
use Exception;
use Waca\ExceptionHandler;
use Waca\Exceptions\CurlException;
use Waca\Helpers\Interfaces\IBlacklistHelper;
use Waca\PdoDatabase;
use Waca\SiteConfiguration;

class BlacklistHelper implements IBlacklistHelper
{
    /** @var HttpHelper */
    private $httpHelper;

    /**
     * Cache of previously requested usernames
     * @var array
     */
    private $cache = array();

    /** @var PdoDatabase */
    private $database;

    /**
     * BlacklistHelper constructor.
     *
     * @param HttpHelper  $httpHelper
     * @param PdoDatabase $database
     */
    public function __construct(HttpHelper $httpHelper, PdoDatabase $database)
    {
        $this->httpHelper = $httpHelper;
        $this->database = $database;
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
        /*if (isset($this->cache[$username])) {
            $result = $this->cache[$username];
            if ($result === false) {
                return false;
            }

            return $result['line'];
        }*/

        try {
            $this->assign('gettingtbl', 'yup');
            $result = $this->performWikiLookup($username);
        }
        catch (CurlException $ex) {
            // LOGME log this, but fail gracefully.
            ExceptionHandler::logExceptionToDisk($ex, $this->siteConfiguration);
            return false;
        }

        if ($result['result'] === 'ok') {
            // not blacklisted
            $this->cache[$username] = false;

            return false;
        }
        else {
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
        // FIXME: domains!
        /** @var Domain $domain */
        $domain = Domain::getById(1, $this->database);
        $this->assign('tbldomain',$domain);
        $endpoint = $domain->getWikiApiPath();
        $this->assign('tblendpoint',$endpoint);
        $parameters = array(
            'action'       => 'titleblacklist',
            'format'       => 'php',
            'tbtitle'      => $username,
            'tbaction'     => 'new-account',
            'tbnooverride' => true,
        );

        $apiResult = $this->httpHelper->get($endpoint, $parameters);

        $data = unserialize($apiResult);
        $this->assign('tbldata',$data);
        return $data['titleblacklist'];
    }
}