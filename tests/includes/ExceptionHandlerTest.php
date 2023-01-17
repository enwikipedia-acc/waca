<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests;

use Waca\ExceptionHandler;
use Waca\Exceptions\ApplicationLogicException;
use Waca\SiteConfiguration;
use PHPUnit_Extensions_MockFunction;
use PHPUnit\Framework\TestCase;
use \ErrorException;

/**
 * @requires extension runkit7
 */
class ExceptionHandlerTest extends TestCase
{
    private $ex;
    private $eh;
    private $ob_mock;

    public function setUp() : void
    {
        if (!extension_loaded('runkit7')) {
            $this->markTestSkipped('Dependencies for test are not available. Please install runkit7/runkit7');

            return;
        }

        // Starting by catching an error
        global $siteConfiguration;

        $siteConfiguration->setDebuggingTraceEnabled(false);

        $siteConfiguration = new SiteConfiguration();

        try {
            throw new ApplicationLogicException("Testing");
        }
        catch (\Exception $e) {
            $this->ex = $e;
        }

        $this->eh = new ExceptionHandler();
    }

    public function tearDown() : void
    {
        global $siteConfiguration;
        unset($siteConfiguration);
        unset($this->eh);
    }

    public function testExceptionHandler()
    {
        ob_start();
        $this->eh->exceptionHandler($this->ex);
        $text = ob_get_clean();

        // We must restart the output buffering for phpunit
        ob_start();

        $this->assertNotNull($text);

        $this->assertStringContainsString("trained monkeys ask", $text);
        $this->assertStringContainsString("Oops! Something went wrong!", $text);
        $this->assertStringNotContainsString("internal.php", $text);
        $this->assertStringNotContainsString("We need a few bits of information ", $text);
    }

    public function testErrorHandler()
    {
        $severity = 3;
        $code = 0;
        $message = "This is a test error";
        $file = "apple.php";
        $line = 2016;

        try {
            $this->eh->errorHandler($severity, $message, $file, $line);
            $this->fail("Expected exception not thrown");
        }
        catch (ErrorException $e) {
            $this->assertNotEquals($e, null);

            $this->assertInstanceOf("ErrorException", $e);

            $this->assertEquals($e->getSeverity(), $severity);
            $this->assertEquals($e->getCode(), $code);
            $this->assertEquals($e->getMessage(), $message);
            $this->assertEquals($e->getFile(), $file);
            $this->assertEquals($e->getLine(), $line);
        }
    }
}
