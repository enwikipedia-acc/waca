<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Security;

use PHPUnit_Framework_TestCase;
use Waca\Security\RoleConfiguration;

class RoleConfigurationTest extends PHPUnit_Framework_TestCase
{
    public function testReturnsOnlyApplicableRoles()
    {
        // arrange
        $roleConfiguration = new RoleConfiguration(
            array(
                'public'    => array(),
                'user'      => array(),
                'admin'     => array(),
                'checkuser' => array(),
            ),
            array('public'));

        // act
        $result = $roleConfiguration->getApplicableRoles(array('public', 'user', 'admin'));

        // assert
        $this->assertEquals(array('public', 'user', 'admin'), array_keys($result));
    }

    public function testReturnsOnlyApplicableRolesWithNonexistent()
    {
        // arrange
        $roleConfiguration = new RoleConfiguration(
            array(
                'public'    => array(),
                'user'      => array(),
                'admin'     => array(),
                'checkuser' => array(),
            ),
            array('public'));

        // act
        $result = $roleConfiguration->getApplicableRoles(array('public', 'user', 'blargh'));

        // assert
        $this->assertEquals(array('public', 'user'), array_keys($result));
    }

    public function testReturnsChildRolesToo()
    {
        // arrange
        $roleConfiguration = new RoleConfiguration(
            array(
                'public'    => array(),
                'user'      => array(),
                'admin'     => array(),
                'checkuser' => array(
                    '_childRoles' => array('admin'),
                ),
            ),
            array('public'));

        // act
        $result = $roleConfiguration->getApplicableRoles(array('public', 'user', 'checkuser'));

        // assert
        $this->assertEquals(array('public', 'user', 'checkuser', 'admin'), array_keys($result));
    }

    public function testAvailableRoles()
    {
        $roleConfiguration = new RoleConfiguration(
            array(
                'public' => array(),
                'loggedIn' => array(),
                'user'   => array(
                    '_description' => 'users',
                    '_editableBy' => array(),),
                'admin'  => array(
                    '_childRoles' => array('foo'),
                    '_description' => 'admins',
                    '_editableBy' => array(),
                ),
                'foo'    => array(
                    '_hidden' => true,
                ),
                'bar'    => array(
                    '_hidden' => true,
                ),
            ),
            array('public'));

        // act
        $result = $roleConfiguration->getAvailableRoles();

        // assert
        $this->assertEquals(array('user', 'admin'), array_keys($result));
    }
}
