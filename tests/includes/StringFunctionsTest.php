<?php

class StringFunctionsTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var StringFunctions
	 */
	private $e;

	public function setUp()
	{
		$this->e = new StringFunctions();
	}

	public function tearDown()
	{
		$this->e = null;
	}

	public function testFormatAsUsername()	{
		// Happy path
		$this->assertEquals($this->e->formatAsUsername("this"), "This");
		$this->assertEquals($this->e->formatAsUsername("1this"), "1this");
		$this->assertEquals($this->e->formatAsUsername("This"), "This");
		$this->assertEquals($this->e->formatAsUsername("This "), "This");
		$this->assertEquals($this->e->formatAsUsername("This_"), "This");

		// Sad Path
		$this->assertNotEquals($this->e->formatAsUsername("This "), "This ");
		$this->assertNotEquals($this->e->formatAsUsername("This_"), "This_");
		$this->assertNotEquals($this->e->formatAsUsername("this"), "this");
		$this->assertNotEquals($this->e->formatAsUsername("1this"), "1This");
	}

	public function testFormatAsEmail()
	{
		$this->assertEquals($this->e->formatAsEmail("this@example.com"), "this@example.com");
		$this->assertEquals($this->e->formatAsEmail("1this12345@example.com"), "1this12345@example.com");
		$this->assertEquals($this->e->formatAsEmail(" 1this12345@example.com"), "1this12345@example.com");
		$this->assertEquals($this->e->formatAsEmail("1this12345@example.com "), "1this12345@example.com");
		$this->assertEquals($this->e->formatAsEmail("1this12345 @example.com"), "1this12345@example.com");
		$this->assertEquals($this->e->formatAsEmail("1this12345@ example.com"), "1this12345@example.com");

		// Sad Path
		$this->assertNotEquals($this->e->formatAsEmail(" 1this12345@example.com"), " 1this12345@example.com");
		$this->assertNotEquals($this->e->formatAsEmail("1this12345@example.com "), "1this12345@example.com ");
		$this->assertNotEquals($this->e->formatAsEmail("1this12345 @example.com"), "1this12345 @example.com");
		$this->assertNotEquals($this->e->formatAsEmail("1this12345@ example.com"), "1this12345@ example.com");
	}
}
