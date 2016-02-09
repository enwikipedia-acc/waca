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
	 *
	 * @param SiteConfiguration $configuration
	 * @param HttpHelper        $http
	 */
	public function __construct(SiteConfiguration $configuration, HttpHelper $http)
	{
		$this->configuration = $configuration;
		$this->http = $http;
	}

	/**
	 * Gets the HTML for the provided wiki-markup from the MediaWiki service endpoint
	 *
	 * @param string $wikiText
	 *
	 * @return string
	 */
	public function getHtmlForWikiText($wikiText)
	{
		$endpoint = $this->configuration->getMediawikiWebServiceEndpoint();

		$parameters = array(
			'action'             => 'parse',
			'pst'                => true,
			'contentmodel'       => 'wikitext',
			'disablelimitreport' => true,
			'disabletoc'         => true,
			'disableeditsection' => true,
			'format'             => 'php',
			'text'               => $wikiText,
		);

		$apiResult = $this->http->get($endpoint, $parameters);
		$parseResult = unserialize($apiResult);

		return $parseResult['parse']['text']['*'];
	}
}