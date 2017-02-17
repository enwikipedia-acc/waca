<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 12/24/16
 * Time: 00:52
 */

namespace Waca\Tests;

use Waca\Exceptions\EnvironmentException;
use Waca\PdoDatabase;

class PdoDatabaseTest extends \PHPUnit_Framework_TestCase
{
	/** @var PdoDatabase */
	private $pdb;

	public function setUp()
	{
		$this->pdb = $this->getMock("PdoDatabase", array('get_database', 'getDatabaseConnection','get_arguments'));
	}

	public function testGetDatabaseConnection()
	{
		global $cDatabaseConfig;
        var_dump($cDatabaseConfig);
		$connectionName = "test";

		$cDatabaseConfig[$connectionName]["dsrcname"] = "testing";
		$cDatabaseConfig[$connectionName]["username"] = "one_crazy_guy";
		$cDatabaseConfig[$connectionName]["password"] = "iDidn'tDoIt123";

		try {
			$this->pdb->getDatabaseConnection("TotallyDoesntExist");
		}
		catch(EnvironmentException $e) {
			$this->assertEquals($e->getMessage(), "Database configuration not found for alias TotallyDoesn'tExist");
		}
	}
}
