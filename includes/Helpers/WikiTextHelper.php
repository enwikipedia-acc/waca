<?php

namespace Waca\Helpers;

use Waca\SiteConfiguration;

class WikiTextHelper
{
	/**
	 * @var SiteConfiguration
	 */
	private $configuration;
	/**
	 * @var HttpHelper
	 */
	private $http;

	/**
	 * WikiTextHelper constructor.
	 * @param SiteConfiguration $configuration
	 * @param HttpHelper        $http
	 */
	public function __construct(SiteConfiguration $configuration, HttpHelper $http)
	{
		$this->configuration = $configuration;
		$this->http = $http;
	}

	public function getHtmlForWikiText($wikiText)
	{
		$endpoint = $this->configuration->getMediawikiWebServiceEndpoint();

		$url = $endpoint . '?action=parse&pst&contentmodel=wikitext&disablelimitreport&disabletoc&disableeditsection&format=php&text=' . urlencode($wikiText);
		$apiResult = $this->http->get($url);
		$parseResult = unserialize($apiResult);

		return $parseResult['parse']['text']['*'];
	}
}