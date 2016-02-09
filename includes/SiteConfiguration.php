<?php

namespace Waca;

/**
 * Class SiteConfiguration
 *
 * IMPORTANT: This class must never throw an exception or trigger an error. It's used in the error handler.
 *
 * @package Waca
 */
class SiteConfiguration
{
	private $baseUrl;
	private $filePath;
	private $schemaVersion = 17;
	private $debuggingTraceEnabled;
	private $dataClearIp = '127.0.0.1';
	private $dataClearEmail = 'acc@toolserver.org';
	private $forceIdentification = true;
	private $mediawikiScriptPath = "https://en.wikipedia.org/w/index.php";
	private $mediawikiWebServiceEndpoint = "";
	private $enforceOAuth = true;
	private $emailConfirmationEnabled = true;
	private $miserModeLimit = 25;
	private $requestStates = array(
		'Open'          => array(
			'defertolog' => 'users', // don't change or you'll break old logs
			'deferto'    => 'users',
			'header'     => 'Open requests',
			'api'        => "open",
		),
		'Flagged users' => array(
			'defertolog' => 'flagged users', // don't change or you'll break old logs
			'deferto'    => 'flagged users',
			'header'     => 'Flagged user needed',
			'api'        => "admin",
		),
		'Checkuser'     => array(
			'defertolog' => 'checkusers', // don't change or you'll break old logs
			'deferto'    => 'checkusers',
			'header'     => 'Checkuser needed',
			'api'        => "checkuser",
		),
	);
	private $squidList = array();
	private $defaultCreatedTemplateId = 1;
	private $defaultRequestStateKey = 'Open';
	private $defaultRequestDeferredStateKey = 'Flagged users';
	private $useStrictTransportSecurity = false;
	private $userAgent = 'Wikipedia-ACC Tool/0.1 (+https://accounts.wmflabs.org/internal.php/team)';
	private $curlDisableVerifyPeer = false;

	/**
	 * Gets the base URL of the tool
	 *
	 * If the internal page of the tool is at http://localhost/path/internal.php, this would be set to
	 * http://localhost/path
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->baseUrl;
	}

	/**
	 * @param string $baseUrl
	 *
	 * @return SiteConfiguration
	 */
	public function setBaseUrl($baseUrl)
	{
		$this->baseUrl = $baseUrl;

		return $this;
	}

	/**
	 * Path on disk to the directory containing the tool's code
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->filePath;
	}

	/**
	 * @param string $filePath
	 *
	 * @return SiteConfiguration
	 */
	public function setFilePath($filePath)
	{
		$this->filePath = $filePath;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getSchemaVersion()
	{
		return $this->schemaVersion;
	}

	/**
	 * @return mixed
	 */
	public function getDebuggingTraceEnabled()
	{
		return $this->debuggingTraceEnabled;
	}

	/**
	 * @param mixed $debuggingTraceEnabled
	 *
	 * @return SiteConfiguration
	 */
	public function setDebuggingTraceEnabled($debuggingTraceEnabled)
	{
		$this->debuggingTraceEnabled = $debuggingTraceEnabled;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDataClearIp()
	{
		return $this->dataClearIp;
	}

	/**
	 * @param string $dataClearIp
	 *
	 * @return SiteConfiguration
	 */
	public function setDataClearIp($dataClearIp)
	{
		$this->dataClearIp = $dataClearIp;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDataClearEmail()
	{
		return $this->dataClearEmail;
	}

	/**
	 * @param string $dataClearEmail
	 *
	 * @return SiteConfiguration
	 */
	public function setDataClearEmail($dataClearEmail)
	{
		$this->dataClearEmail = $dataClearEmail;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getForceIdentification()
	{
		return $this->forceIdentification;
	}

	/**
	 * @param boolean $forceIdentification
	 *
	 * @return SiteConfiguration
	 */
	public function setForceIdentification($forceIdentification)
	{
		$this->forceIdentification = $forceIdentification;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMediawikiScriptPath()
	{
		return $this->mediawikiScriptPath;
	}

	/**
	 * @param string $mediawikiScriptPath
	 *
	 * @return SiteConfiguration
	 */
	public function setMediawikiScriptPath($mediawikiScriptPath)
	{
		$this->mediawikiScriptPath = $mediawikiScriptPath;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMediawikiWebServiceEndpoint()
	{
		return $this->mediawikiWebServiceEndpoint;
	}

	/**
	 * @param string $mediawikiWebServiceEndpoint
	 *
	 * @return SiteConfiguration
	 */
	public function setMediawikiWebServiceEndpoint($mediawikiWebServiceEndpoint)
	{
		$this->mediawikiWebServiceEndpoint = $mediawikiWebServiceEndpoint;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getEnforceOAuth()
	{
		return $this->enforceOAuth;
	}

	/**
	 * @param boolean $enforceOAuth
	 *
	 * @return SiteConfiguration
	 */
	public function setEnforceOAuth($enforceOAuth)
	{
		$this->enforceOAuth = $enforceOAuth;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getEmailConfirmationEnabled()
	{
		return $this->emailConfirmationEnabled;
	}

	/**
	 * @param boolean $emailConfirmationEnabled
	 *
	 * @return $this
	 */
	public function setEmailConfirmationEnabled($emailConfirmationEnabled)
	{
		$this->emailConfirmationEnabled = $emailConfirmationEnabled;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getMiserModeLimit()
	{
		return $this->miserModeLimit;
	}

	/**
	 * @param int $miserModeLimit
	 *
	 * @return SiteConfiguration
	 */
	public function setMiserModeLimit($miserModeLimit)
	{
		$this->miserModeLimit = $miserModeLimit;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getRequestStates()
	{
		return $this->requestStates;
	}

	/**
	 * @param array $requestStates
	 *
	 * @return SiteConfiguration
	 */
	public function setRequestStates($requestStates)
	{
		$this->requestStates = $requestStates;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getSquidList()
	{
		return $this->squidList;
	}

	/**
	 * @param array $squidList
	 *
	 * @return SiteConfiguration
	 */
	public function setSquidList($squidList)
	{
		$this->squidList = $squidList;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getDefaultCreatedTemplateId()
	{
		return $this->defaultCreatedTemplateId;
	}

	/**
	 * @param int $defaultCreatedTemplateId
	 *
	 * @return SiteConfiguration
	 */
	public function setDefaultCreatedTemplateId($defaultCreatedTemplateId)
	{
		$this->defaultCreatedTemplateId = $defaultCreatedTemplateId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultRequestStateKey()
	{
		return $this->defaultRequestStateKey;
	}

	/**
	 * @param string $defaultRequestStateKey
	 *
	 * @return SiteConfiguration
	 */
	public function setDefaultRequestStateKey($defaultRequestStateKey)
	{
		$this->defaultRequestStateKey = $defaultRequestStateKey;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultRequestDeferredStateKey()
	{
		return $this->defaultRequestDeferredStateKey;
	}

	/**
	 * @param string $defaultRequestDeferredStateKey
	 *
	 * @return SiteConfiguration
	 */
	public function setDefaultRequestDeferredStateKey($defaultRequestDeferredStateKey)
	{
		$this->defaultRequestDeferredStateKey = $defaultRequestDeferredStateKey;

		return $this;
	}

	/**
	 * @param boolean $useStrictTransportSecurity
	 *
	 * @return SiteConfiguration
	 */
	public function setUseStrictTransportSecurity($useStrictTransportSecurity)
	{
		$this->useStrictTransportSecurity = $useStrictTransportSecurity;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getUseStrictTransportSecurity()
	{
		return $this->useStrictTransportSecurity;
	}

	/**
	 * @param string $userAgent
	 *
	 * @return SiteConfiguration
	 */
	public function setUserAgent($userAgent)
	{
		$this->userAgent = $userAgent;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUserAgent()
	{
		return $this->userAgent;
	}

	/**
	 * @param boolean $curlDisableVerifyPeer
	 *
	 * @return SiteConfiguration
	 */
	public function setCurlDisableVerifyPeer($curlDisableVerifyPeer)
	{
		$this->curlDisableVerifyPeer = $curlDisableVerifyPeer;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getCurlDisableVerifyPeer()
	{
		return $this->curlDisableVerifyPeer;
	}
}