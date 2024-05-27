<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests\Security;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit\Framework\TestCase;
use Waca\DataObjects\User;
use Waca\IdentificationVerifier;
use Waca\Security\RoleConfiguration;
use Waca\Security\SecurityManager;

/**
 * Class SecurityManagerTest
 * @package  Waca\Tests
 * @category Security-Critical
 */
class SecurityManagerTest extends TestCase
{
    /** @var User|PHPUnit_Framework_MockObject_MockObject */
    private $user;
    /** @var IdentificationVerifier|PHPUnit_Framework_MockObject_MockObject */
    private $identificationVerifier;

    public function setUp() : void
    {
        $this->user = $this->getMockBuilder(User::class)->getMock();

        $this->identificationVerifier = $this->getMockBuilder(IdentificationVerifier::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testPublicAccess()
    {
        // arrange
        $page = 'Waca\\Page\\PageTest';
        /** @var RoleConfiguration|PHPUnit_Framework_MockObject_MockObject */
        $roleConfiguration = $this->getMockBuilder(RoleConfiguration::class)->getMock();
        $roleConfiguration->method('getApplicableRoles')->willReturn(array(
            'public' => array(
                $page => array(
                    RoleConfiguration::ALL => RoleConfiguration::ACCESS_ALLOW,
                ),
            ),
        ));
        $roleConfiguration->method('roleNeedsIdentification')->willReturn(false);

        $securityManager = new SecurityManager($this->identificationVerifier, $roleConfiguration);
        $this->identificationVerifier->method('isUserIdentified')->willReturn(true);

        // act
        $result = $securityManager->allows($page, 'main', User::getCommunity());

        // assert
        $this->assertEquals(SecurityManager::ALLOWED, $result);
    }

    public function testPublicAccessDenied()
    {
        // arrange
        $page = 'Waca\\Page\\PageTest';
        /** @var RoleConfiguration|PHPUnit_Framework_MockObject_MockObject */
        $roleConfiguration = $this->getMockBuilder(RoleConfiguration::class)->getMock();
        $roleConfiguration->method('getApplicableRoles')->willReturn(array(
            'public' => array(
                $page => array(
                    RoleConfiguration::ALL => RoleConfiguration::ACCESS_DENY,
                ),
            ),
        ));
        $roleConfiguration->method('roleNeedsIdentification')->willReturn(false);

        $securityManager = new SecurityManager($this->identificationVerifier, $roleConfiguration);
        $this->identificationVerifier->method('isUserIdentified')->willReturn(true);

        // act
        $result = $securityManager->allows($page, 'main', User::getCommunity());

        // assert
        $this->assertEquals(SecurityManager::ERROR_DENIED, $result);
    }
}
