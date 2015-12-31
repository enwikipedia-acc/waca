<?php

class stringsTest extends PHPUnit_Framework_TestCase
{
    private $e;

	public function setUp()
	{

		$this->e = new strings();
	}

	public function tearDown() {
        $this->e = NULL;
    }

    public function test_struname() {
        // Happy path
        $this->assertEquals($this->e->struname("this"), "This");
        $this->assertEquals($this->e->struname("1this"), "1this");
        $this->assertEquals($this->e->struname("This"), "This");
        $this->assertEquals($this->e->struname("This "), "This");
        $this->assertEquals($this->e->struname("This_"), "This");

        // Sad Path
        $this->assertNotEquals($this->e->struname("This "), "This ");
        $this->assertNotEquals($this->e->struname("This_"), "This_");
        $this->assertNotEquals($this->e->struname("this"), "this");
        $this->assertNotEquals($this->e->struname("1this"), "1This");
    }

    public function test_stremail() {
        $this->assertEquals($this->e->stremail("this@example.com"), "this@example.com");
        $this->assertEquals($this->e->stremail("1this12345@example.com"), "1this12345@example.com");
        $this->assertEquals($this->e->stremail(" 1this12345@example.com"), "1this12345@example.com");
        $this->assertEquals($this->e->stremail("1this12345@example.com "), "1this12345@example.com");
        $this->assertEquals($this->e->stremail("1this12345 @example.com"), "1this12345@example.com");
        $this->assertEquals($this->e->stremail("1this12345@ example.com"), "1this12345@example.com");

        // Sad Path
        $this->assertNotEquals($this->e->stremail(" 1this12345@example.com"), " 1this12345@example.com");
        $this->assertNotEquals($this->e->stremail("1this12345@example.com "), "1this12345@example.com ");
        $this->assertNotEquals($this->e->stremail("1this12345 @example.com"), "1this12345 @example.com");
        $this->assertNotEquals($this->e->stremail("1this12345@ example.com"), "1this12345@ example.com");

    }

}
