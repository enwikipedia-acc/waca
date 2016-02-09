<?php

namespace Waca\Tests\Helpers;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Waca\Helpers\HttpHelper;
use Waca\Helpers\WikiTextHelper;
use Waca\SiteConfiguration;

class WikiTextHelperTest extends PHPUnit_Framework_TestCase
{
	public function testReturnsWikiText()
	{
		/** @var SiteConfiguration|PHPUnit_Framework_MockObject_MockObject $config */
		$config = $this->getMockBuilder(SiteConfiguration::class)->getMock();
		$config->method('getMediawikiWebServiceEndpoint')->willReturn('http://example.local/w/api.php');

		$content = 'Hello world';
		$data = serialize(array('parse' => array('text' => array('*' => $content))));

		/** @var HttpHelper|PHPUnit_Framework_MockObject_MockObject $http */
		$http = $this->getMockBuilder(HttpHelper::class)->disableOriginalConstructor()->getMock();
		$http->method('get')->willReturn($data);

		$helper = new WikiTextHelper($config, $http);
		$result = $helper->getHtmlForWikiText('foo');

		$this->assertEquals($content, $result);
	}
}