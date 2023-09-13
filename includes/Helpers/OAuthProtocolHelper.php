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
    private $authUrl;
    /**
     * @var PdoDatabase
     */
    private $database;
    /**
     * @var string
     */
    private $consumerKey;
    /**
     * @var string
     */
    private $consumerSecret;
    /**
     * @var string
     */
    private $userAgent;

    /**
     * OAuthHelper constructor.
     *
     * @param string     $consumerKey
     * @param string     $consumerSecret
     * @param PdoDatabase $database
     * @param string      $userAgent
     */
    public function __construct(
        $consumerKey,
        $consumerSecret,
        PdoDatabase $database,
        $userAgent
    ) {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->userAgent = $userAgent;
        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function getRequestToken()
    {
        /** @var Token $requestToken */

        /** @var Domain $domain */
        $domain = Domain::getCurrent($this->database);

        list($authUrl, $requestToken) = $this->getClient($domain)->initiate();
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

        /** @var Domain $domain */
        $domain = Domain::getCurrent($this->database);

        return $this->getClient($domain)->complete($requestToken, $oauthVerifier);
    }

    /**
     * @inheritDoc
     */
    public function getIdentityTicket($oauthAccessToken, $oauthAccessSecret)
    {
        /** @var Domain $domain */
        $domain = Domain::getCurrent($this->database);

        return $this->getClient($domain)->identify(new Token($oauthAccessToken, $oauthAccessSecret));
    }

    /**
     * @inheritDoc
     */
    public function apiCall($apiParams, $accessToken, $accessSecret, $method = 'GET', string $apiPath)
    {
        $userToken = new Token($accessToken, $accessSecret);

        $apiParams['format'] = 'json';

        if ($apiParams === null || !is_array($apiParams)) {
            throw new CurlException("Invalid API call");
        }

        /** @var Domain $domain */
        $domain = Domain::getByApiPath($apiPath, $this->database);

        $url = $apiPath;
        $isPost = ($method === 'POST');

        if ($method === 'GET') {
            $query = http_build_query($apiParams);
            $url .= '?' . $query;
            $apiParams = null;
        }

        $data = $this->getClient($domain)->makeOAuthCall($userToken, $url, $isPost, $apiParams);

        return json_decode($data);
    }

    /**
     * @param string $oauthEndpoint
     *
     * @return Client
     */
    protected function getClient(Domain $domain) : Client
    {
        $oauthClientConfig = new ClientConfig($domain->getWikiArticlePath() . "?title=Special:OAuth");
        $oauthClientConfig->setConsumer(new Consumer($this->consumerKey, $this->consumerSecret));
        $oauthClientConfig->setUserAgent($this->userAgent);
        return new Client($oauthClientConfig);
    }
}
