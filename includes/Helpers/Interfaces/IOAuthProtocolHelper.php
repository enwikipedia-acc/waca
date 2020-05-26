<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers\Interfaces;

use Exception;
use stdClass;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\CurlException;

interface IOAuthProtocolHelper
{
    /**
     * @return stdClass
     *
     * @throws Exception
     * @throws CurlException
     */
    public function getRequestToken();

    /**
     * @param string $requestToken
     *
     * @return string
     */
    public function getAuthoriseUrl($requestToken);

    /**
     * @param string $oauthRequestToken
     * @param string $oauthRequestSecret
     * @param string $oauthVerifier
     *
     * @return stdClass
     * @throws CurlException
     * @throws Exception
     */
    public function callbackCompleted($oauthRequestToken, $oauthRequestSecret, $oauthVerifier);

    /**
     * @param string $oauthAccessToken
     * @param string $oauthAccessSecret
     *
     * @return stdClass
     * @throws CurlException
     * @throws Exception
     */
    public function getIdentityTicket($oauthAccessToken, $oauthAccessSecret);

    /**
     * @param array  $apiParams    array of parameters to send to the API
     * @param string $accessToken  user's access token
     * @param string $accessSecret user's secret
     * @param string $method       HTTP method
     *
     * @return stdClass
     * @throws ApplicationLogicException
     * @throws CurlException
     * @throws Exception
     */
    public function apiCall($apiParams, $accessToken, $accessSecret, $method = 'GET');
}