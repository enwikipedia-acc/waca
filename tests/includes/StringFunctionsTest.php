<?php

namespace Waca\Tests;

use PHPUnit_Framework_TestCase;
use Waca\StringFunctions;

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

	public function testFormatAsUsername()
	{
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

	public function testIsMultibyte()
	{
		$emptyString = '';
		$this->assertFalse($this->e->isMultibyte($emptyString));

		$nullString = null;
		$this->assertFalse($this->e->isMultibyte($nullString));

		$nbspString = html_entity_decode('&nbsp;');
		$this->assertTrue($this->e->isMultibyte($nbspString));

		$numberString = '12345';
		$this->assertFalse($this->e->isMultibyte($numberString));

		$asciiString = 'abcd';
		$utf8String = 'àbcd';
		$this->assertFalse($this->e->isMultibyte($asciiString));
		$this->assertTrue($this->e->isMultibyte($utf8String));

		$greekString = 'ΣΙΛΟΝ';
		$tibetanString = '༆༇༂༖';
		$chineseString = '专世丳儽';
		$this->assertTrue($this->e->isMultibyte($greekString));
		$this->assertTrue($this->e->isMultibyte($tibetanString));
		$this->assertTrue($this->e->isMultibyte($chineseString));
	}

	public function testUcFirst(){
		$this->assertEquals('Abc', $this->e->ucfirst('abc'));
		$this->assertEquals('ABC', $this->e->ucfirst('ABC'));
		$this->assertEquals('123', $this->e->ucfirst('123'));

		$this->assertEquals('Trần Nguyễn Minh Huy', $this->e->ucfirst('Trần Nguyễn Minh Huy'));
		$this->assertEquals('和平奮鬥救地球', $this->e->ucfirst('和平奮鬥救地球'));
	}
}
