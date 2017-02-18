<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use PHPUnit_Extensions_MockFunction;
use PHPUnit_Framework_TestCase;
use Waca\Helpers\DebugHelper;

class DebugHelperTest extends PHPUnit_Framework_TestCase
{
    /** @var DebugHelper */
    private $dbh;

    public function setUp()
    {
        $this->dbh = $this->getMockBuilder(DebugHelper::class)->setMethods(["get_debug_backtrace"])->getMock();
        $this->dbh->method('get_debug_backtrace')->willReturn(
            array(
                array(
                    "file"     => "/tmp/a.php",
                    "line"     => 10,
                    "function" => "a_test",
                    "args"     => array("friend"),
                ),
                array(
                    "file"     => "/tmp/b.php",
                    "line"     => 2,
                    "function" => "b_test",
                    "args"     => array("/tmp/a.php"),
                ),
                array(
                    "file"     => "/tmp/c.php",
                    "line"     => 64,
                    "function" => "c_test",
                    "args"     => array("/tmp/a.php"),
                ),
                array(
                    "file"     => "/tmp/d.php",
                    "line"     => 128,
                    "function" => "d_test",
                    "args"     => array("/tmp/a.php"),
                ),
            )
        );
    }

    public function tearDown()
    {
        $this->dbh = null;
    }

    public function testGetBacktrace()
    {
        $this->assertContains("/tmp/c.php", $this->dbh->getBacktrace());
        $this->assertContains("/tmp/d.php", $this->dbh->getBacktrace());
        $this->assertContains("d_test", $this->dbh->getBacktrace());

        $this->assertNotContains("/tmp/a.php", $this->dbh->getBacktrace());
        $this->assertNotContains("/tmp/b.php", $this->dbh->getBacktrace());
        $this->assertNotContains("b_test", $this->dbh->getBacktrace());
    }
}
