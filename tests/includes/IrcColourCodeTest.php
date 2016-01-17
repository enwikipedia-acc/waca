<?php

class IrcColourCodeTest extends PHPUnit_Framework_TestCase
{
	private $irc;

	public function setUp()
	{
		$this->irc = new IrcColourCode();
	}

	public function tearDown()
	{
		$this->irc = null;
	}

	public function testColorCodes()
	{
		$i = $this->irc;

		$this->assertEquals($i::BOLD, "\x02");
		$this->assertEquals($i::ITALIC, "\x09");
		$this->assertEquals($i::STRIKE, "\x13");
		$this->assertEquals($i::UNDERLINE, "\x15");
		$this->assertEquals($i::UNDERLINE2, "\x1f");
		$this->assertEquals($i::REVERSE, "\x16");
		$this->assertEquals($i::RESET, "\x0f");

		$this->assertEquals($i::WHITE, "\x0300");
		$this->assertEquals($i::BLACK, "\x0301");
		$this->assertEquals($i::DARK_BLUE, "\x0302");
		$this->assertEquals($i::DARK_GREEN, "\x0303");
		$this->assertEquals($i::RED, "\x0304");
		$this->assertEquals($i::DARK_RED, "\x0305");
		$this->assertEquals($i::DARK_VIOLET, "\x0306");
		$this->assertEquals($i::ORANGE, "\x0307");
		$this->assertEquals($i::YELLOW, "\x0308");
		$this->assertEquals($i::LIGHT_GREEN, "\x0309");
		$this->assertEquals($i::CYAN, "\x0310");
		$this->assertEquals($i::LIGHT_CYAN, "\x0311");
		$this->assertEquals($i::BLUE, "\x0312");
		$this->assertEquals($i::VIOLET, "\x0313");
		$this->assertEquals($i::DARK_GREY, "\x0314");
		$this->assertEquals($i::LIGHT_GREY, "\x0315");

		$this->assertNotEquals($i::BOLD, "\x021");
		$this->assertNotEquals($i::ITALIC, "\x091");
		$this->assertNotEquals($i::STRIKE, "\x131");
		$this->assertNotEquals($i::UNDERLINE, "\x151");
		$this->assertNotEquals($i::UNDERLINE2, "\x1f1");
		$this->assertNotEquals($i::REVERSE, "\x161");
		$this->assertNotEquals($i::RESET, "\x0f1");

		$this->assertNotEquals($i::WHITE, "\x03001");
		$this->assertNotEquals($i::BLACK, "\x03011");
		$this->assertNotEquals($i::DARK_BLUE, "\x03021");
		$this->assertNotEquals($i::DARK_GREEN, "\x03031");
		$this->assertNotEquals($i::RED, "\x03041");
		$this->assertNotEquals($i::DARK_RED, "\x03051");
		$this->assertNotEquals($i::DARK_VIOLET, "\x03061");
		$this->assertNotEquals($i::ORANGE, "\x03071");
		$this->assertNotEquals($i::YELLOW, "\x03081");
		$this->assertNotEquals($i::LIGHT_GREEN, "\x03091");
		$this->assertNotEquals($i::CYAN, "\x03101");
		$this->assertNotEquals($i::LIGHT_CYAN, "\x03111");
		$this->assertNotEquals($i::BLUE, "\x03121");
		$this->assertNotEquals($i::VIOLET, "\x03131");
		$this->assertNotEquals($i::DARK_GREY, "\x03141");
		$this->assertNotEquals($i::LIGHT_GREY, "\x03151");
	}

}
