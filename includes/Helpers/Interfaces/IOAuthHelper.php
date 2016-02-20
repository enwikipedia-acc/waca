<?php

namespace Waca\Helpers\Interfaces;

use Exception;
use JWT;
use stdClass;
use Waca\Exceptions\CurlException;

interface IOAuthHelper
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
	 * @return JWT
	 * @throws CurlException
	 * @throws Exception
	 */
	public function getIdentityTicket($oauthAccessToken, $oauthAccessSecret);
}