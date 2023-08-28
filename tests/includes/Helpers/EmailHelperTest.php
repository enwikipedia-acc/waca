<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use PHPUnit_Extensions_MockFunction;
use PHPUnit\Framework\TestCase;
use Waca\Helpers\EmailHelper;

/**
 * @requires extension runkit7
 */
class EmailHelperTest extends TestCase
{
    /** @var PHPUnit_Extensions_MockFunction */
    private $mailMock;
    /** @var EmailHelper */
    private $emailHelper;

    public function setUp() : void
    {
        $this->markTestSkipped("runkit-based tests broken since PHPUnit upgrade");

        if (!extension_loaded('runkit7')) {
            $this->markTestSkipped('Dependencies for test are not available. Please install runkit7/runkit7');

            return;
        }

        $this->emailHelper = new EmailHelper("sender@example.com", "phpunit");

        $this->mailMock = new PHPUnit_Extensions_MockFunction('mail', $this->emailHelper);
    }

    public function testSendMail()
    {
        $this->mailMock->expects($this->once())
            ->with('noreply@stwalkerster.co.uk', 'test mail subject', 'test mail content',
                "From: sender@example.com\r\n")
            ->will($this->returnValue(true));

        $this->emailHelper->sendMail('sender@example.com', 'noreply@stwalkerster.co.uk', 'test mail subject', 'test mail content');
    }

    public function testSendMailWithHeader()
    {
        $this->mailMock->expects($this->once())
            ->with('noreply@stwalkerster.co.uk', 'test mail subject', 'test mail content',
                "X-ACC-Test: foobar\r\nFrom: sender@example.com\r\n")
            ->will($this->returnValue(true));

        $this->emailHelper->sendMail('sender@example.com', 'noreply@stwalkerster.co.uk', 'test mail subject', 'test mail content',
            array('X-ACC-Test' => 'foobar'));
    }

    public function tearDown() : void
    {
        if (extension_loaded('runkit7')) {
            // restore functionality
            $this->mailMock->restore();
        }

        parent::tearDown();
    }
}