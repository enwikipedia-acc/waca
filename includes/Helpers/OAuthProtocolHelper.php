<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use MediaWiki\OAuthClient\Client;
use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Token;
use Waca\DataObjects\Domain;
use Waca\Exceptions\CurlException;
use Waca\PdoDatabase;

class OAuthProtocolHelper implements Interfaces\IOAuthProtocolHelper
{
    private $oauthClient;

    private $authUrl;
    /**
     * @var PdoDatabase
     */
    private $database;

    /**
     * OAuthHelper constructor.
     *
     * @param string      $oauthEndpoint
     * @param string      $consumerKey
     * @param string      $consumerSecret
     * @param string      $mediawikiWebServiceEndpoint
     * @param PdoDatabase $database
     * @param string      $userAgent
     */
    public function __construct(
        $oauthEndpoint,
        $consumerKey,
        $consumerSecret,
        $mediawikiWebServiceEndpoint,
        PdoDatabase $database,
        $userAgent
    ) {
        $oauthClientConfig = new ClientConfig($oauthEndpoint);
        $oauthClientConfig->setUserAgent($userAgent);
        $oauthClientConfig->setConsumer(new Consumer($consumerKey, $consumerSecret));

        $this->oauthClient = new Client($oauthClientConfig);
        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function getRequestToken()
    {
        /** @var Token $requestToken */
        list($authUrl, $requestToken) = $this->oauthClient->initiate();
        $this->authUrl = $authUrl;
        return $requestToken;
    }

    /**
     * @inheritDoc
     */
    public function getAuthoriseUrl($requestToken)
    {
        return $this->authUrl;
    }

    /**
     * @inheritDoc
     */
    public function callbackCompleted($oauthRequestToken, $oauthRequestSecret, $oauthVerifier)
    {
        $requestToken = new Token($oauthRequestToken, $oauthRequestSecret);

        return $this->oauthClient->complete($requestToken, $oauthVerifier);
    }

    /**
     * @inheritDoc
     */
    public function getIdentityTicket($oauthAccessToken, $oauthAccessSecret)
    {
        return $this->oauthClient->identify(new Token($oauthAccessToken, $oauthAccessSecret));
    }

    /**
     * @inheritDoc
     */
    public function apiCall($apiParams, $accessToken, $accessSecret, $method = 'GET')
    {
        $userToken = new Token($accessToken, $accessSecret);

        $apiParams['format'] = 'json';

        if ($apiParams === null || !is_array($apiParams)) {
            throw new CurlException("Invalid API call");
        }

        // FIXME: domains!
        /** @var Domain $domain */
        $domain = Domain::getById(1, $this->database);

        $url = $domain->getWikiApiPath();
        $isPost = ($method === 'POST');

        if ($method === 'GET') {
            $query = http_build_query($apiParams);
            $url .= '?' . $query;
            $apiParams = null;
        }

        $data = $this->oauthClient->makeOAuthCall($userToken, $url, $isPost, $apiParams);

        return json_decode($data);
    }
}
