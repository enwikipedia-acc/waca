<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use PHPUnit_Extensions_MockFunction;
use PHPUnit\Framework\TestCase;
use Waca\Helpers\EmailHelper;

/**
 * @requires extension runkit
 */
class EmailHelperTest extends TestCase
{
    /** @var PHPUnit_Extensions_MockFunction */
    private $mailMock;
    /** @var EmailHelper */
    private $emailHelper;

    public function setUp()
    {
        if (!extension_loaded('runkit')) {
            $this->markTestSkipped('Dependencies for test are not available. Please install zenovich/runkit');

            return;
        }

        $this->emailHelper = new EmailHelper();

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

    public function tearDown()
    {
        if (extension_loaded('runkit')) {
            // restore functionality
            $this->mailMock->restore();
        }

        parent::tearDown();
    }
}