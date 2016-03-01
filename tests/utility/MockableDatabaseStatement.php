<?php

namespace Waca\Tests\Utility;

use PDOStatement;

class MockableDatabaseStatement extends PDOStatement
{
	public function __construct()
	{
	}
}