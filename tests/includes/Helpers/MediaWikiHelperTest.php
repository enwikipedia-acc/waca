<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use MediaWiki\OAuthClient\Util;
use PHPUnit\Framework\TestCase;
use Waca\Helpers\Interfaces\IMediaWikiClient;
use Waca\Helpers\MediaWikiHelper;
use Waca\SiteConfiguration;

class MediaWikiHelperTest extends TestCase
{
    private array $calls = [];

    private function getHelper(): MediaWikiHelper
    {
        $this->calls = [];

        $client = new class($this->calls) implements IMediaWikiClient {
            private array $calls;

            public function __construct(array &$calls)
            {
                $this->calls = &$calls;
            }

            public function doApiCall($params, $method): object
            {
                $this->calls[] = [$params, $method];

                switch ($params['action']) {
                    case 'query':
                        if ($params['meta'] === 'authmanagerinfo') {
                            return (object)['query' => (object)['authmanagerinfo' => (object)['requests' => []]]];
                        }

                        return (object)['query' => (object)['tokens' => (object)[
                            'createaccounttoken' => 'createtoken+\\',
                            'csrftoken'          => 'csrftoken+\\',
                        ]]];
                    case 'createaccount':
                        return (object)['createaccount' => (object)['status' => 'PASS']];
                    case 'edit':
                        return (object)['edit' => (object)['result' => 'Success']];
                }

                throw new \LogicException('Unexpected API action ' . $params['action']);
            }
        };

        $siteConfiguration = $this->createMock(SiteConfiguration::class);
        $siteConfiguration->method('getBaseUrl')->willReturn('https://example.com');

        return new MediaWikiHelper($client, $siteConfiguration);
    }

    /**
     * Asserts that every POST made can be OAuth-signed, i.e. contains only strings (or arrays of strings).
     */
    private function assertPostParametersAreSignable(): void
    {
        $posts = array_filter($this->calls, function ($call) {
            return $call[1] === 'POST';
        });

        $this->assertNotEmpty($posts, 'Expected at least one POST call');

        foreach ($posts as list($params, $method)) {
            foreach ($params as $key => $value) {
                $this->assertIsString($value, "POST parameter '{$key}' must be a string, not " . gettype($value));
            }

            $this->assertIsString(Util::buildHttpQuery($params));
        }
    }

    public function testCreateAccountSendsOnlyStringPostParameters()
    {
        $this->getHelper()->createAccount('Example', 'user@example.com', 'A reason');

        $this->assertPostParametersAreSignable();

        $createCall = end($this->calls);
        $this->assertEquals('createaccount', $createCall[0]['action']);
        $this->assertEquals('1', $createCall[0]['mailpassword']);
    }

    public function testAddTalkPageMessageCreateOnlySendsOnlyStringPostParameters()
    {
        $this->getHelper()->addTalkPageMessage('Example', 'Welcome', 'summary', 'message', true);

        $this->assertPostParametersAreSignable();

        $editCall = end($this->calls);
        $this->assertEquals('edit', $editCall[0]['action']);
        $this->assertEquals('1', $editCall[0]['createonly']);
    }

    public function testAddTalkPageMessageWithoutCreateOnlySendsOnlyStringPostParameters()
    {
        $this->getHelper()->addTalkPageMessage('Example', 'Welcome', 'summary', 'message', false);

        $this->assertPostParametersAreSignable();

        $editCall = end($this->calls);
        $this->assertArrayNotHasKey('createonly', $editCall[0]);
    }
}
