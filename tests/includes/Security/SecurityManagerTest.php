<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests\Security;

use Closure;
use PHPUnit\Framework\TestCase;
use Waca\DataObjects\User;
use Waca\IIdentificationVerifier;
use Waca\Security\ISecurityManager;
use Waca\Security\IUserRoleLoader;
use Waca\Security\RoleConfigurationBase;
use Waca\Security\SecurityManager;

/**
 * Class SecurityManagerTest
 * @package  Waca\Tests
 * @category Security-Critical
 */
class SecurityManagerTest extends TestCase
{
    private $user;

    private IIdentificationVerifier $identificationVerifier;
    private RoleConfigurationBase $roleConfig;
    private IUserRoleLoader $userRoleLoader;
    private Closure $needsIdCallback;

    public function setUp() : void
    {
        $this->user = $this->getMockBuilder(User::class)->getMock();

        $this->identificationVerifier = $this->getMockBuilder(IIdentificationVerifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->roleConfig = $this->getMockBuilder(RoleConfigurationBase::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->userRoleLoader = $this->getMockBuilder(IUserRoleLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->needsIdCallback = function($role) {
            if ($role === 'loggedIn' || $role === 'public') {
                return false;
            }

            return true;
        };
    }

    public function testAvailableRoles() {
        // arrange
        $availableRolesData = [
            'admin'    => [],
            'toolRoot' => [],
        ];

        $this->roleConfig->method('getAvailableRoles')->willReturn($availableRolesData);

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $availableRoles = $secMan->getAvailableRoles();

        // assert
        $this->assertEquals($availableRolesData, $availableRoles);
    }

    public function testGetActiveRoles() {
        // arrange
        $this->userRoleLoader->method('loadRolesForUser')->willReturn(['admin']);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('isIdentified')->willReturn(true);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $secMan->getActiveRoles($this->user, $retrievedActiveRoles, $retrievedInactiveRoles);

        // assert

        // implicit roles
        $this->assertContains('public', $retrievedActiveRoles);
        $this->assertContains('loggedIn', $retrievedActiveRoles);
        $this->assertContains('user', $retrievedActiveRoles);

        // explicit roles
        $this->assertContains('admin', $retrievedActiveRoles);

        // check there's nothing extra
        $this->assertCount(4, $retrievedActiveRoles);
        $this->assertCount(0, $retrievedInactiveRoles);
    }

    public function testGetActiveRolesInactiveUser() {
        // arrange
        $this->userRoleLoader->method('loadRolesForUser')->willReturn(['admin']);
        $this->user->method('isActive')->willReturn(false);
        $this->user->method('isIdentified')->willReturn(true);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $secMan->getActiveRoles($this->user, $retrievedActiveRoles, $retrievedInactiveRoles);

        // assert

        // implicit roles
        $this->assertContains('public', $retrievedActiveRoles);
        $this->assertContains('loggedIn', $retrievedActiveRoles);

        // check there's nothing extra
        $this->assertCount(2, $retrievedActiveRoles);

        // inactive users don't have inactive roles
        $this->assertCount(0, $retrievedInactiveRoles);
    }

    public function testGetActiveRolesNonIDUser() {
        // arrange
        $this->userRoleLoader->method('loadRolesForUser')->willReturn(['admin']);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('isIdentified')->willReturn(false);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $secMan->getActiveRoles($this->user, $retrievedActiveRoles, $retrievedInactiveRoles);

        // assert

        // implicit roles
        $this->assertContains('public', $retrievedActiveRoles);
        $this->assertContains('loggedIn', $retrievedActiveRoles);

        // roles locked behind id flag
        $this->assertContains('user', $retrievedInactiveRoles);
        $this->assertContains('admin', $retrievedInactiveRoles);

        // check there's nothing extra
        $this->assertCount(2, $retrievedActiveRoles);
        $this->assertCount(2, $retrievedInactiveRoles);
    }

    public function testGetActiveRolesImplicitOnly() {
        // arrange
        $this->userRoleLoader->method('loadRolesForUser')->willReturn([]);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('isIdentified')->willReturn(true);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $secMan->getActiveRoles($this->user, $retrievedActiveRoles, $retrievedInactiveRoles);

        // assert

        // implicit roles
        $this->assertContains('public', $retrievedActiveRoles);
        $this->assertContains('loggedIn', $retrievedActiveRoles);
        $this->assertContains('user', $retrievedActiveRoles);

        // check there's nothing extra
        $this->assertCount(3, $retrievedActiveRoles);
        $this->assertCount(0, $retrievedInactiveRoles);
    }

    public function testGetActiveRolesCommunityUser() {
        // arrange
        $this->userRoleLoader->method('loadRolesForUser')->willReturn([]);

        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $secMan->getActiveRoles(User::getCommunity(), $retrievedActiveRoles, $retrievedInactiveRoles);

        // assert

        // implicit roles
        $this->assertContains('public', $retrievedActiveRoles);

        // check there's nothing extra
        $this->assertCount(1, $retrievedActiveRoles);
        $this->assertCount(0, $retrievedInactiveRoles);
    }

    public function testCaching() {
        // arrange
        $this->userRoleLoader->expects($this->once())->method('loadRolesForUser')->willReturn([]);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('isIdentified')->willReturn(true);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $secMan->getCachedActiveRoles($this->user, $retrievedActiveRoles, $retrievedInactiveRoles);
        $secMan->getCachedActiveRoles($this->user, $cachedActiveRoles, $cachedInactiveRoles);

        // assert
        $this->assertEquals($retrievedInactiveRoles, $cachedInactiveRoles);
        $this->assertEquals($retrievedActiveRoles, $cachedActiveRoles);

        $this->userRoleLoader->method('loadRolesForUser');
    }

    public function testAllowsAllowed()
    {
        // arrange
        $this->userRoleLoader->expects($this->once())->method('loadRolesForUser')->willReturn([]);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('isIdentified')->willReturn(true);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));
        $this->roleConfig->method('getResultantRole')->willReturn([
            'PageA' => [
                RoleConfigurationBase::MAIN => RoleConfigurationBase::ACCESS_ALLOW,
                'private'                   => RoleConfigurationBase::ACCESS_DENY,
            ],
        ]);

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $result = $secMan->allows('PageA', RoleConfigurationBase::MAIN, $this->user);

        // assert
        $this->assertEquals(ISecurityManager::ALLOWED, $result);
    }

    public function testAllowsDenied()
    {
        // arrange
        $this->userRoleLoader->expects($this->once())->method('loadRolesForUser')->willReturn([]);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('isIdentified')->willReturn(true);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));
        $this->roleConfig->method('getResultantRole')->willReturn([
            'PageA' => [
                RoleConfigurationBase::MAIN => RoleConfigurationBase::ACCESS_ALLOW,
                'private'                   => RoleConfigurationBase::ACCESS_DENY,
            ],
        ]);

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $result = $secMan->allows('PageA', 'private', $this->user);

        // assert
        $this->assertEquals(ISecurityManager::ERROR_DENIED, $result);
    }

    public function testAllowsNotKnown()
    {
        // arrange
        $this->userRoleLoader->expects($this->once())->method('loadRolesForUser')->willReturn([]);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('isIdentified')->willReturn(true);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));
        $this->roleConfig->method('getResultantRole')->willReturn([
            'PageA' => [
                RoleConfigurationBase::MAIN => RoleConfigurationBase::ACCESS_ALLOW,
                'private'                   => RoleConfigurationBase::ACCESS_DENY,
            ],
        ]);

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $result = $secMan->allows('PageNonExistent', RoleConfigurationBase::MAIN, $this->user);

        // assert
        $this->assertEquals(ISecurityManager::ERROR_DENIED, $result);
    }

    public function testAllowsDefault()
    {
        // arrange
        $this->userRoleLoader->expects($this->once())->method('loadRolesForUser')->willReturn([]);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('isIdentified')->willReturn(true);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));
        $this->roleConfig->method('getResultantRole')->willReturn([
            'PageA' => [
                RoleConfigurationBase::MAIN => RoleConfigurationBase::ACCESS_DEFAULT,
            ],
        ]);

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $result = $secMan->allows('PageA', RoleConfigurationBase::MAIN, $this->user);

        // assert
        $this->assertEquals(ISecurityManager::ERROR_DENIED, $result);
    }

    public function testAllowsNotID()
    {
        // arrange
        $this->userRoleLoader->expects($this->once())->method('loadRolesForUser')->willReturn([]);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('isIdentified')->willReturn(false);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));
        $this->roleConfig->method('getResultantRole')->willReturnOnConsecutiveCalls(
            [
                'PageA' => [
                    RoleConfigurationBase::MAIN => RoleConfigurationBase::ACCESS_ALLOW,
                ],
            ],
            [
                'PageA' => [
                    RoleConfigurationBase::MAIN => RoleConfigurationBase::ACCESS_ALLOW,
                    'private'                   => RoleConfigurationBase::ACCESS_ALLOW,
                ],
            ]
        );

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $result = $secMan->allows('PageA', 'private', $this->user);

        // assert
        $this->assertEquals(ISecurityManager::ERROR_NOT_IDENTIFIED, $result);
    }

    public function testAllowsWithAllDeny()
    {
        // arrange
        $this->userRoleLoader->expects($this->once())->method('loadRolesForUser')->willReturn([]);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('isIdentified')->willReturn(true);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));
        $this->roleConfig->method('getResultantRole')->willReturnOnConsecutiveCalls(
            [
                'PageA' => [
                    RoleConfigurationBase::ALL => RoleConfigurationBase::ACCESS_DENY,
                    RoleConfigurationBase::MAIN => RoleConfigurationBase::ACCESS_ALLOW,
                ],
            ]
        );

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $result = $secMan->allows('PageA', RoleConfigurationBase::MAIN, $this->user);

        // assert
        // despite an 'allow' being granted, the 'deny' on all should override.
        $this->assertEquals(ISecurityManager::ERROR_DENIED, $result);
    }

    public function testAllowsWithSpecificDeny()
    {
        // arrange
        $this->userRoleLoader->expects($this->once())->method('loadRolesForUser')->willReturn([]);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('isIdentified')->willReturn(true);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));
        $this->roleConfig->method('getResultantRole')->willReturnOnConsecutiveCalls(
            [
                'PageA' => [
                    RoleConfigurationBase::ALL => RoleConfigurationBase::ACCESS_ALLOW,
                    RoleConfigurationBase::MAIN => RoleConfigurationBase::ACCESS_DENY,
                ],
            ]
        );

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $result = $secMan->allows('PageA', RoleConfigurationBase::MAIN, $this->user);

        // assert
        // despite an 'allow' being granted, the 'deny' on all should override.
        $this->assertEquals(ISecurityManager::ERROR_DENIED, $result);
    }

    public function testAllowsWithAllAllow()
    {
        // arrange
        $this->userRoleLoader->expects($this->once())->method('loadRolesForUser')->willReturn([]);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('isIdentified')->willReturn(true);
        $this->roleConfig->method('roleNeedsIdentification')->will($this->returnCallback($this->needsIdCallback));
        $this->roleConfig->method('getResultantRole')->willReturnOnConsecutiveCalls(
            [
                'PageA' => [
                    RoleConfigurationBase::ALL => RoleConfigurationBase::ACCESS_ALLOW,
                ],
            ]
        );

        $secMan = new SecurityManager($this->identificationVerifier, $this->roleConfig, $this->userRoleLoader);

        // act
        $result = $secMan->allows('PageA', 'nonExistent', $this->user);

        // assert
        // even though this action is unknown, allow it anyway because it's an allow on all.
        $this->assertEquals(ISecurityManager::ALLOWED, $result);
    }
}
