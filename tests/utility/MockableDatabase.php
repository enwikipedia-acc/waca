<?php

namespace Waca\Tests\Utility;

use Waca\PdoDatabase;

/**
 * Class MockableDatabase
 *
 * This class allows you to mock a PDO instance without it failing the unit tests on PHP 5.5.
 *
 * @package Waca\Tests\Utility
 */
class MockableDatabase extends PdoDatabase
{
	public function __construct($dsn = null, $username = null, $passwd = null, $options = null)
	{
	}
}