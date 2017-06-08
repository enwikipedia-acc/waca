<?php

class TransactionExceptionTest extends PHPUnit_Framework_TestCase
{

	private $message = 'imamessagestring';

    private $e;

    public function setUp() {
        $this->e = new TransactionException($this->message);
    }

	public function testDefaultConstruction()
	{
		$this->assertEquals(null, $this->e->getPrevious());
	}

    public function testGetTitle() {
        $this->assertEquals('Error occured during transaction', $this->e->getTitle());

        $this->assertNotEquals($this->message, $this->e->getTitle());

        $this->assertNotEquals(null, $this->e->getTitle());
        $this->assertNotEquals("", $this->e->getTitle());
    }

    public function testGetAlertType() {
        $this->assertEquals('alert-error', $this->e->getAlertType());

        $this->assertNotEquals($this->message, $this->e->getAlertType());

        $this->assertNotEquals(null, $this->e->getAlertType());
        $this->assertNotEquals("", $this->e->getAlertType());
    }

    public function testGetCode() {
        $this->assertEquals(0, $this->e->getCode());

        $this->assertNotEquals(1, $this->e->getCode());

        $this->assertNotEquals("", $this->e->getCode());
    }

    public function testGetPrevious() {
        $this->assertEquals(null, $this->e->getPrevious());

        $this->assertNotEquals(1, $this->e->getPrevious());

        $this->assertNotEquals($this->message, $this->e->getPrevious());
    }

    public function testGetMessage() {
        $this->assertEquals($this->message, $this->e->getMessage());
        $this->assertNotEquals("", $this->e->getMessage());
        $this->assertNotEquals(null, $this->e->getMessage());

    }
}
