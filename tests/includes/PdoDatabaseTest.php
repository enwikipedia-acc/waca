<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests;

use PHPUnit\Framework\TestCase;
use Waca\Exceptions\EnvironmentException;
use Waca\PdoDatabase;
use Waca\SiteConfiguration;

class PdoDatabaseTest extends TestCase
{
    /** @var PdoDatabase */
    private $pdb;
    
    public function setUp() : void
    {
        $this->markTestIncomplete("Mocking issues.");
        $this->pdb = $this->getMock("PdoDatabase", array('get_database', 'getDatabaseConnection','get_arguments'));
    }

    public function testGetDatabaseConnection()
    {
        global $cDatabaseConfig;

        $cDatabaseConfig["dsrcname"] = "testing";
        $cDatabaseConfig["username"] = "one_crazy_guy";
        $cDatabaseConfig["password"] = "iDidn'tDoIt123";

        try {
            $this->pdb->getDatabaseConnection(new SiteConfiguration());
        }
        catch(EnvironmentException $e) {
            $this->assertEquals($e->getMessage(), "Database configuration not found for alias TotallyDoesn'tExist");
        }
    }
}
