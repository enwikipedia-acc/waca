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
	private $schemaVersion = 16;
	private $debuggingTraceEnabled;
	private $dataClearIp = '127.0.0.1';
	private $dataClearEmail = 'acc@toolserver.org';
	private $forceIdentification = true;
	private $mediawikiScriptPath = "https://en.wikipedia.org/w/index.php";
	private $mediawikiWebServiceEndpoint = "";

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
	 * @return SiteConfiguration
	 */
	public function setForceIdentification($forceIdentification)
	{
		$this->forceIdentification = $forceIdentification;
		return $this;
	}

	/**
	 * @param string $mediawikiScriptPath
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
	public function getMediawikiScriptPath()
	{
		return $this->mediawikiScriptPath;
	}

	/**
	 * @param string $mediawikiWebServiceEndpoint
	 * @return SiteConfiguration
	 */
	public function setMediawikiWebServiceEndpoint($mediawikiWebServiceEndpoint)
	{
		$this->mediawikiWebServiceEndpoint = $mediawikiWebServiceEndpoint;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMediawikiWebServiceEndpoint()
	{
		return $this->mediawikiWebServiceEndpoint;
	}
}