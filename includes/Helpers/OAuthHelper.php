<?php

namespace Waca\Helpers;

use Exception;
use JWT;
use OAuthConsumer;
use OAuthRequest;
use OAuthSignatureMethod_HMAC_SHA1;
use OAuthToken;
use stdClass;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\CurlException;
use Waca\Helpers\Interfaces\IOAuthHelper;

class OAuthHelper implements IOAuthHelper
{
	private $oauthConsumer;
	/**
	 * @var string
	 */
	private $oauthEndpoint;
	/**
	 * @var string
	 */
	private $consumerToken;
	/**
	 * @var string
	 */
	private $consumerSecret;
	/**
	 * @var HttpHelper
	 */
	private $httpHelper;
	/**
	 * @var string
	 */
	private $mediawikiWebServiceEndpoint;

	/**
	 * OAuthHelper constructor.
	 *
	 * @param string     $oauthEndpoint
	 * @param string     $consumerKey
	 * @param string     $consumerSecret
	 * @param HttpHelper $httpHelper
	 * @param string     $mediawikiWebServiceEndpoint
	 */
	public function __construct(
		$oauthEndpoint,
		$consumerKey,
		$consumerSecret,
		HttpHelper $httpHelper,
		$mediawikiWebServiceEndpoint
	) {
		$this->oauthEndpoint = $oauthEndpoint;
		$this->consumerToken = $consumerKey;
		$this->consumerSecret = $consumerSecret;
		$this->httpHelper = $httpHelper;

		$this->oauthConsumer = new OAuthConsumer($this->consumerToken, $this->consumerSecret);
		$this->mediawikiWebServiceEndpoint = $mediawikiWebServiceEndpoint;
	}

	/**
	 * @return stdClass
	 *
	 * @throws Exception
	 * @throws CurlException
	 */
	public function getRequestToken()
	{
		$endpoint = $this->oauthEndpoint . '/initiate&format=json&oauth_callback=oob';

		$parsedUrl = parse_url($endpoint);
		$urlParameters = array();
		parse_str($parsedUrl['query'], $urlParameters);

		$req_req = OAuthRequest::from_consumer_and_token($this->oauthConsumer, null, 'GET', $endpoint, $urlParameters);
		$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
		$req_req->sign_request($hmac_method, $this->oauthConsumer, null);

		$targetUrl = (string)$req_req;

		$data = $this->httpHelper->get($targetUrl, null);

		if ($data === false) {
			throw new Exception('Curl error: ' . $this->httpHelper->getError());
		}

		$token = json_decode($data);

		if (!isset($token)) {
			throw new Exception('Unknown error encountered getting request token while decoding json data.');
		}

		if (isset($token->error)) {
			throw new Exception('Error encountered while getting request token: ' . $token->error);
		}

		return $token;
	}

	/**
	 * @param string $requestToken
	 *
	 * @return string
	 */
	public function getAuthoriseUrl($requestToken)
	{
		return "{$this->oauthEndpoint}/authorize&oauth_token={$requestToken}&oauth_consumer_key={$this->consumerToken}";
	}

	/**
	 * @param string $oauthRequestToken
	 * @param string $oauthRequestSecret
	 * @param string $oauthVerifier
	 *
	 * @return stdClass
	 * @throws CurlException
	 * @throws Exception
	 */
	public function callbackCompleted($oauthRequestToken, $oauthRequestSecret, $oauthVerifier)
	{
		$endpoint = $this->oauthEndpoint . '/token&format=json';

		$requestConsumer = new OAuthConsumer($oauthRequestToken, $oauthRequestSecret);

		$parsedUrl = parse_url($endpoint);
		parse_str($parsedUrl['query'], $urlParameters);
		$urlParameters['oauth_verifier'] = trim($oauthVerifier);

		$acc_req = OAuthRequest::from_consumer_and_token($this->oauthConsumer, $requestConsumer, 'GET', $endpoint,
			$urlParameters);
		$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
		$acc_req->sign_request($hmac_method, $this->oauthConsumer, $requestConsumer);

		$targetUrl = (string)$acc_req;

		$data = $this->httpHelper->get($targetUrl, null);

		if ($data === false) {
			throw new Exception('Curl error: ' . $this->httpHelper->getError());
		}

		$token = json_decode($data);

		if (!isset($token)) {
			throw new Exception('Unknown error encountered getting access token while decoding json data.');
		}

		if (isset($token->error)) {
			throw new Exception('Error encountered while getting access token: ' . $token->error);
		}

		return $token;
	}

	/**
	 * @param string $oauthAccessToken
	 * @param string $oauthAccessSecret
	 *
	 * @return JWT
	 * @throws CurlException
	 * @throws Exception
	 */
	public function getIdentityTicket($oauthAccessToken, $oauthAccessSecret)
	{
		$endpoint = $this->oauthEndpoint . '/identify&format=json';

		$oauthToken = new OAuthToken($oauthAccessToken, $oauthAccessSecret);

		$parsedUrl = parse_url($endpoint);
		parse_str($parsedUrl['query'], $urlParameters);

		$acc_req = OAuthRequest::from_consumer_and_token($this->oauthConsumer, $oauthToken, 'GET', $endpoint,
			$urlParameters);
		$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
		$acc_req->sign_request($hmac_method, $this->oauthConsumer, $oauthToken);

		$targetUrl = (string)$acc_req;

		$data = $this->httpHelper->get($targetUrl, null);

		if ($data === false) {
			throw new Exception('Curl error: ' . $this->httpHelper->getError());
		}

		$decodedData = json_decode($data);

		if (isset($decodedData->error)) {
			throw new Exception($decodedData->error);
		}

		$identity = JWT::decode($data, $this->consumerSecret);

		return $identity;
	}

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
	public function apiCall($apiParams, $accessToken, $accessSecret, $method = 'GET')
	{
		$userToken = new OAuthToken($accessToken, $accessSecret);

		$apiParams['format'] = 'json';

		$api_req = OAuthRequest::from_consumer_and_token(
			$this->oauthConsumer, // Consumer
			$userToken, // User Access Token
			$method, // HTTP Method
			$this->mediawikiWebServiceEndpoint, // Endpoint url
			$apiParams    // Extra signed parameters
		);

		$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();

		$api_req->sign_request($hmac_method, $this->oauthConsumer, $userToken);

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
			throw new Exception('Curl error: ' . $this->httpHelper->getError());
		}

		return json_decode($data);
	}
}
