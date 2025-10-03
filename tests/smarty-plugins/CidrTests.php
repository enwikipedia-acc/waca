<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests\SmartyPlugins;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function smarty_modifier_cidr;

class CidrTests extends TestCase
{
    public function testIPv4Null()
    {
        $input = '192.168.1.2';
        $cidr = null;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals($input, $actual);
    }

    public function testIPv4c32()
    {
        $input = '192.168.1.2';
        $cidr = 32;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals($input, $actual);
    }

    public function testIPv4c30()
    {
        $input = '192.168.1.2';
        $cidr = 30;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals('192.168.1.0', $actual);
    }

    public function testIPv4c23()
    {
        $input = '192.168.1.2';
        $cidr = 23;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals('192.168.0.0', $actual);
    }

    public function testIPv4c16()
    {
        $input = '192.168.1.2';
        $cidr = 16;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals('192.168.0.0', $actual);
    }

    public function testIPv4c12()
    {
        $input = '192.168.1.2';
        $cidr = 12;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals('192.160.0.0', $actual);
    }

    public function testIPv4c0()
    {
        $input = '192.168.1.2';
        $cidr = 0;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals('0.0.0.0', $actual);
    }

    public function testIPv4cn0()
    {
        $input = '192.168.1.2';
        $cidr = -1;

        $this->expectException(InvalidArgumentException::class);;
        $actual = smarty_modifier_cidr($input, $cidr);
    }

    public function testIPv4cn33()
    {
        $input = '192.168.1.2';
        $cidr = 33;

        $this->expectException(InvalidArgumentException::class);;
        $actual = smarty_modifier_cidr($input, $cidr);
    }

    public function testIPv6Null()
    {
        $input = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
        $cidr = null;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals('2001:db8:85a3::', $actual);
    }

    public function testIPv6NullCollapsed()
    {
        $input = '2001:db8:85a3::8a2e:370:7334';
        $cidr = null;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals('2001:db8:85a3::', $actual);
    }

    public function testIPv6c128()
    {
        $input = '2001:db8:85a3::8a2e:370:7334';
        $cidr = 128;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals('2001:db8:85a3::8a2e:370:7334', $actual);
    }

    public function testIPv6c64()
    {
        $input = '2001:db8:85a3:1:2:8a2e:370:7334';
        $cidr = 64;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals('2001:db8:85a3:1::', $actual);
    }

    public function testIPv6c48()
    {
        $input = '2001:db8:85a3:1:2:8a2e:370:7334';
        $cidr = 48;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals('2001:db8:85a3::', $actual);
    }

    public function testIPv6c32()
    {
        $input = '2001:db8:85a3:1:2:8a2e:370:7334';
        $cidr = 32;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals('2001:db8::', $actual);
    }

    public function testIPv6c63()
    {
        $input = '2001:db8:85a3:11:2:8a2e:370:7334';
        $cidr = 63;

        $actual = smarty_modifier_cidr($input, $cidr);

        $this->assertEquals('2001:db8:85a3:10::', $actual);
    }

    public function testIPv6cn0()
    {
        $input = '::1';
        $cidr = -1;

        $this->expectException(InvalidArgumentException::class);;
        $actual = smarty_modifier_cidr($input, $cidr);
    }

    public function testIPv6cn129()
    {
        $input = '::1';
        $cidr = 129;

        $this->expectException(InvalidArgumentException::class);;
        $actual = smarty_modifier_cidr($input, $cidr);
    }

}