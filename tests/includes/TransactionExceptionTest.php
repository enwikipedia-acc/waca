<?php

use PHPUnit\Framework\TestCase;

class TransactionExceptionTest extends TestCase
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
