<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit_Extensions_MockFunction;
use \Waca\Offline;

class OfflineTest extends TestCase
{
    public function setUp() : void
    {
        if (!extension_loaded('runkit7')) {
            $this->markTestSkipped('Dependencies for test are not available. Please install runkit7/runkit7');

            return;
        }

        global $dontUseDb;

        $dontUseDb = true;
    }

    public function testIsOffline()
    {
        global $siteConfiguration;

        $offline = new Offline();

        $siteConfiguration->setOffline(['offline' => true]);

        $this->assertEquals($offline->isOffline($siteConfiguration), true);

        $siteConfiguration->setOffline(['offline' => false]);

        $this->assertEquals($offline->isOffline($siteConfiguration), false);

    }

    public function testGetOfflineMessage()
    {
        $this->markTestSkipped("runkit-based tests broken since PHPUnit upgrade");

        $external = false;
        $message = "This is a test message.";

        $offMock = new PHPUnit_Extensions_MockFunction('header', $this);
        $offMock->expects($this->any())
            ->with("HTTP/1.1 503 Service Unavailable")
            ->will($this->returnValue(true));

        ob_start();

        print $offline->getOfflineMessage($external, $message);

        $text1 = ob_get_contents();

        ob_end_clean();

        $this->assertNotContains("We’re very sorry, but the account creation request tool is currently offline while critical maintenance is performed.",
            $text1);
        $this->assertContains($message, $text1);

        $this->assertContains("After much experimentation, someone finally managed to kill ACC.", $text1);

        $external = true;

        ob_start();

        print $offline->getOfflineMessage($external, $message);

        $text2 = ob_get_contents();

        ob_end_clean();

        $this->assertContains("We’re very sorry, but the account creation request tool is currently offline while critical maintenance is performed.",
            $text2);
        $this->assertNotContains($message, $text2);

        $this->assertNotContains("After much experimentation, someone finally managed to kill ACC.", $text2);
    }
}
