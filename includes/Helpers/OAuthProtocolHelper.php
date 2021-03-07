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
use Waca\Exceptions\CurlException;

class OAuthProtocolHelper implements Interfaces\IOAuthProtocolHelper
{
    private $oauthClient;

    private $mediawikiWebServiceEndpoint;

    private $authUrl;

    /**
     * OAuthHelper constructor.
     *
     * @param string     $oauthEndpoint
     * @param string     $consumerKey
     * @param string     $consumerSecret
     * @param string     $mediawikiWebServiceEndpoint
     */
    public function __construct(
        $oauthEndpoint,
        $consumerKey,
        $consumerSecret,
        $mediawikiWebServiceEndpoint
    ) {
        $this->mediawikiWebServiceEndpoint = $mediawikiWebServiceEndpoint;

        $oauthClientConfig = new ClientConfig($oauthEndpoint);
        $oauthClientConfig->setConsumer(new Consumer($consumerKey, $consumerSecret));

        $this->oauthClient = new Client($oauthClientConfig);
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

        $url = $this->mediawikiWebServiceEndpoint;
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