<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\CurlException;
use Waca\Exceptions\MediaWikiApiException;
use Waca\Helpers\Interfaces\IMediaWikiClient;
use Waca\SiteConfiguration;

class BotMediaWikiClient implements IMediaWikiClient
{
    /**
     * @var HttpHelper
     */
    private $httpHelper;
    /** @var string */
    private $mediawikiWebServiceEndpoint;
    /** @var string */
    private $creationBotUsername;
    /** @var string */
    private $creationBotPassword;
    /** @var bool */
    private $knownLoggedIn = false;

    /**
     * BotMediaWikiClient constructor.
     *
     * @param SiteConfiguration $siteConfiguration
     */
    public function __construct(SiteConfiguration $siteConfiguration)
    {
        $this->mediawikiWebServiceEndpoint = $siteConfiguration->getMediawikiWebServiceEndpoint();

        $this->creationBotUsername = $siteConfiguration->getCreationBotUsername();
        $this->creationBotPassword = $siteConfiguration->getCreationBotPassword();

        $this->httpHelper = new HttpHelper(
            $siteConfiguration,
            $siteConfiguration->getCurlCookieJar()
        );
    }

    public function doApiCall($apiParams, $method = 'GET')
    {
        $this->ensureLoggedIn();
        $apiParams['assert'] = 'user';

        return $this->callApi($apiParams, $method);
    }

    private function ensureLoggedIn()
    {
        if ($this->knownLoggedIn) {
            return;
        }

        $userinfoResult = $this->callApi(array('action' => 'query', 'meta' => 'userinfo'), 'GET');
        if (isset($userinfoResult->query->userinfo->anon)) {
            // not logged in.
            $this->logIn();

            // retest
            $userinfoResult = $this->callApi(array('action' => 'query', 'meta' => 'userinfo'), 'GET');
            if (isset($userinfoResult->query->userinfo->anon)) {
                throw new MediaWikiApiException('Unable to log in.');
            }
            else {
                $this->knownLoggedIn = true;
            }
        }
        else {
            $this->knownLoggedIn = true;
        }
    }

    /**
     * @param $apiParams
     * @param $method
     *
     * @return mixed
     * @throws ApplicationLogicException
     * @throws CurlException
     */
    private function callApi($apiParams, $method)
    {
        $apiParams['format'] = 'json';

        if ($method == 'GET') {
            $data = $this->httpHelper->get($this->mediawikiWebServiceEndpoint, $apiParams);
        }
        elseif ($method == 'POST') {
            $data = $this->httpHelper->post($this->mediawikiWebServiceEndpoint, $apiParams);
        }
        else {
            throw new ApplicationLogicException('Unsupported HTTP Method');
        }

        if ($data === false) {
            throw new CurlException('Curl error: ' . $this->httpHelper->getError());
        }

        $result = json_decode($data);

        return $result;
    }

    private function logIn()
    {
        // get token
        $tokenParams = array(
            'action' => 'query',
            'meta'   => 'tokens',
            'type'   => 'login',
        );

        $response = $this->callApi($tokenParams, 'POST');

        if (isset($response->error)) {
            throw new MediaWikiApiException($response->error->code . ': ' . $response->error->info);
        }

        $token = $response->query->tokens->logintoken;

        if ($token === null) {
            throw new MediaWikiApiException('Edit token could not be acquired');
        }

        $params = array(
            'action' => 'login',
            'lgname' => $this->creationBotUsername,
            'lgpassword' => $this->creationBotPassword,
            'lgtoken' => $token,
        );

        $loginResponse = $this->callApi($params, 'POST');

        if ($loginResponse->login->result == 'Success') {
            return;
        }

        throw new ApplicationLogicException(json_encode($loginResponse));
    }
}
