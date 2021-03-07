<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

use PDO;

use Waca\Exceptions\CurlException;
use Waca\Exceptions\EnvironmentException;
use Waca\Helpers\HttpHelper;

/**
 * Class IdentificationVerifier
 *
 * Handles automatically verifying if users are identified with the Wikimedia Foundation or not.  Intended to be used
 * as necessary by the User class when a user's "forceidentified" attribute is NULL.
 *
 * @package  Waca
 * @author   Andrew "FastLizard4" Adams
 * @category Security-Critical
 */
class IdentificationVerifier
{
    /**
     * This field is an array of parameters, in key => value format, that should be appended to the Meta Wikimedia
     * Web Service Endpoint URL to query if a user is listed on the Identification Noticeboard.  Note that URL encoding
     * of these values is *not* necessary; this is done automatically.
     *
     * @var string[]
     * @category Security-Critical
     */
    private static $apiQueryParameters = array(
        'action'   => 'query',
        'format'   => 'json',
        'prop'     => 'links',
        // Populated from SiteConfiguration->getIdentificationNoticeboardPage
        'titles'   => '',
        // Username of the user to be checked, with User: prefix, goes here!  Set in isIdentifiedOnWiki()
        'pltitles' => '',
    );
    /** @var HttpHelper */
    private $httpHelper;
    /** @var SiteConfiguration */
    private $siteConfiguration;
    /** @var PdoDatabase */
    private $dbObject;

    /**
     * IdentificationVerifier constructor.
     *
     * @param HttpHelper        $httpHelper
     * @param SiteConfiguration $siteConfiguration
     * @param PdoDatabase       $dbObject
     */
    public function __construct(HttpHelper $httpHelper, SiteConfiguration $siteConfiguration, PdoDatabase $dbObject)
    {
        $this->httpHelper = $httpHelper;
        $this->siteConfiguration = $siteConfiguration;
        $this->dbObject = $dbObject;
    }

    /**
     * Checks if the given user is identified to the Wikimedia Foundation.
     *
     * @param string $onWikiName The Wikipedia username of the user
     *
     * @return bool
     * @category Security-Critical
     * @throws EnvironmentException
     */
    public function isUserIdentified($onWikiName)
    {
        if ($this->checkIdentificationCache($onWikiName)) {
            return true;
        }
        else {
            if ($this->isIdentifiedOnWiki($onWikiName)) {
                $this->cacheIdentificationStatus($onWikiName);

                return true;
            }
            else {
                return false;
            }
        }
    }

    /**
     * Checks if the given user has a valid entry in the idcache table.
     *
     * @param string $onWikiName The Wikipedia username of the user
     *
     * @return bool
     * @category Security-Critical
     */
    private function checkIdentificationCache($onWikiName)
    {
        $interval = $this->siteConfiguration->getIdentificationCacheExpiry();

        $query = <<<SQL
			SELECT COUNT(`id`)
			FROM `idcache`
			WHERE `onwikiusername` = :onwikiname
				AND DATE_ADD(`checktime`, INTERVAL {$interval}) >= NOW();
SQL;
        $stmt = $this->dbObject->prepare($query);
        $stmt->bindValue(':onwikiname', $onWikiName, PDO::PARAM_STR);
        $stmt->execute();

        // Guaranteed by the query to only return a single row with a single column
        $results = $stmt->fetch(PDO::FETCH_NUM);

        // I don't expect this to ever be a value other than 0 or 1 since the `onwikiusername` column is declared as a
        // unique key - but meh.
        return $results[0] != 0;
    }

    /**
     * Does pretty much exactly what it says on the label - this method will clear all expired idcache entries from the
     * idcache table.  Meant to be called periodically by a maintenance script.
     *
     * @param SiteConfiguration $siteConfiguration
     * @param PdoDatabase       $dbObject
     *
     * @return void
     */
    public static function clearExpiredCacheEntries(SiteConfiguration $siteConfiguration, PdoDatabase $dbObject)
    {
        $interval = $siteConfiguration->getIdentificationCacheExpiry();

        $query = <<<SQL
			DELETE FROM `idcache`
			WHERE DATE_ADD(`checktime`, INTERVAL {$interval}) < NOW();
SQL;
        $dbObject->prepare($query)->execute();
    }

    /**
     * This method will add an entry to the idcache that the given Wikipedia user has been verified as identified.  This
     * is so we don't have to hit the API every single time we check.  The cache entry is valid for as long as specified
     * in the ACC configuration (validity enforced by checkIdentificationCache() and clearExpiredCacheEntries()).
     *
     * @param string $onWikiName The Wikipedia username of the user
     *
     * @return void
     * @category Security-Critical
     */
    private function cacheIdentificationStatus($onWikiName)
    {
        $query = <<<SQL
			INSERT INTO `idcache`
				(`onwikiusername`)
			VALUES
				(:onwikiname)
			ON DUPLICATE KEY UPDATE
				`onwikiusername` = VALUES(`onwikiusername`),
				`checktime` = CURRENT_TIMESTAMP;
SQL;
        $stmt = $this->dbObject->prepare($query);
        $stmt->bindValue(':onwikiname', $onWikiName, PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Queries the Wikimedia API to determine if the specified user is listed on the identification noticeboard.
     *
     * @param string $onWikiName The Wikipedia username of the user
     *
     * @return bool
     * @throws EnvironmentException
     * @category Security-Critical
     */
    private function isIdentifiedOnWiki($onWikiName)
    {
        $strings = new StringFunctions();

        // First character of Wikipedia usernames is always capitalized.
        $onWikiName = $strings->upperCaseFirst($onWikiName);

        $parameters = self::$apiQueryParameters;
        $parameters['pltitles'] = "User:" . $onWikiName;
        $parameters['titles'] = $this->siteConfiguration->getIdentificationNoticeboardPage();

        try {
            $endpoint = $this->siteConfiguration->getMetaWikimediaWebServiceEndpoint();
            $response = $this->httpHelper->get($endpoint, $parameters);
            $response = json_decode($response, true);
        } 
        catch (CurlException $ex) {
            // failed getting identification status, so throw a nicer error.
            $message = 'Could not contact metawiki API to determine user\' identification status. '
                . 'This is probably a transient error, so please try again.';

            throw new EnvironmentException($message);
        }

        $page = @array_pop($response['query']['pages']);

        return @$page['links'][0]['title'] === "User:" . $onWikiName;
    }
}
