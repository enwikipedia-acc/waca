<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\SmartyPlugins;

use PHPUnit\Framework\TestCase;

class TimeSpanTests extends TestCase
{
    public function testSecondsOnly()
    {

        $input = 42;
        $output = \smarty_modifier_timespan($input);

        $this->assertEquals('42s', $output);
    }

    public function testMinSec()
    {
        $input = 142;
        $output = \smarty_modifier_timespan($input);

        $this->assertEquals('2m 22s', $output);
    }

    public function testHourMinSec()
    {
        $input = 142 + (60*60);
        $output = \smarty_modifier_timespan($input);

        $this->assertEquals('1h 2m', $output);
    }


    public function testDayHourMinSec()
    {
        $input = 142 + (60*60) + (2*24*60*60);
        $output = \smarty_modifier_timespan($input);

        $this->assertEquals('2d 1h', $output);
    }

    public function testWeekDayHourMinSec()
    {
        $input = 142 + (60*60) + (2*24*60*60) + (3*7*24*60*60);
        $output = \smarty_modifier_timespan($input);

        $this->assertEquals('3w 2d', $output);
    }

    public function testWeekMin()
    {
        $input = 300 + (3*7*24*60*60);
        $output = \smarty_modifier_timespan($input);

        $this->assertEquals('3w 5m', $output);
    }
}