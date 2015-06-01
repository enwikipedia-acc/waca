<?php

class TransactionExceptionTest extends PHPUnit_Framework_TestCase
{

	public function testDefaultConstruction()
	{
		$message = 'imamessagestring';

		$e = new TransactionException($message);

		$this->assertEquals('Error occured during transaction', $e->getTitle());
		$this->assertEquals('alert-error', $e->getAlertType());
		$this->assertEquals(0, $e->getCode());
		$this->assertEquals(null, $e->getPrevious());
	}

}
