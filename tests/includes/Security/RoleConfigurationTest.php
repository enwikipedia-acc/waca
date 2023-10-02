<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests\Security;

use PHPUnit\Framework\TestCase;
use Waca\Security\RoleConfigurationBase;

class RoleConfigurationTest extends TestCase
{
    private array $roleConfig;

    protected function setUp(): void
    {
        // Reset roleConfig
        $this->roleConfig = [
            'public'   => [
                'PageA' => [
                    'main' => RoleConfigurationBase::ACCESS_ALLOW,
                ],
            ],
            'loggedIn' => [
                'PageB' => [
                    'main' => RoleConfigurationBase::ACCESS_ALLOW,
                ],
            ],
            'user'     => [
                'PageC' => [
                    'main' => RoleConfigurationBase::ACCESS_ALLOW,
                ],
            ],
        ];
    }

    public function testGetAvailableRoles()
    {
        // arrange
        $roleConfig = $this->roleConfig;
        $roleConfig['admin'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            'PageAdmin'    => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig['example'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            '_hidden'      => true,
            'PageHidden'   => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $idExempt = [];

        $roleConfig = new class($roleConfig, $idExempt) extends RoleConfigurationBase {
            public function __construct($r, $i)
            {
                parent::__construct($r, $i);
            }
        };

        // act
        $availableRoles = $roleConfig->getAvailableRoles();

        // assert
        $this->assertCount(1, $availableRoles);
        $this->assertArrayHasKey('admin', $availableRoles);

        // shouldn't return a hidden role
        $this->assertArrayNotHasKey('example', $availableRoles);

        // shouldn't return an implicit role
        $this->assertArrayNotHasKey('user', $availableRoles);
        $this->assertArrayNotHasKey('public', $availableRoles);
        $this->assertArrayNotHasKey('loggedIn', $availableRoles);
    }

    public function testIdentificationExemptRole()
    {
        // arrange
        $idExempt = ['public', 'loggedIn'];

        $roleConfig = new class($this->roleConfig, $idExempt) extends RoleConfigurationBase {
            public function __construct($r, $i)
            {
                parent::__construct($r, $i);
            }
        };

        // act; assert
        // exempt roles from above
        $this->assertFalse($roleConfig->roleNeedsIdentification('public'));
        $this->assertFalse($roleConfig->roleNeedsIdentification('loggedIn'));

        // non-exempt role not listed
        $this->assertTrue($roleConfig->roleNeedsIdentification('user'));

        // non-existent role still needs identification
        $this->assertTrue($roleConfig->roleNeedsIdentification('nonExistent'));
    }

    public function testResultantRoleBasic()
    {
        // arrange
        $roleConfig = $this->roleConfig;
        $roleConfig['admin'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            'PageAdmin'    => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig['example'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            '_hidden'      => true,
            'PageHidden'   => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig = new class($roleConfig) extends RoleConfigurationBase {
            public function __construct($r)
            {
                parent::__construct($r, []);
            }
        };

        // act
        $resultantRole = $roleConfig->getResultantRole(['public']);

        // assert
        $this->assertArrayHasKey('PageA', $resultantRole);
        $this->assertArrayNotHasKey('PageB', $resultantRole);
        $this->assertArrayNotHasKey('PageC', $resultantRole);
        $this->assertArrayNotHasKey('PageAdmin', $resultantRole);
        $this->assertArrayNotHasKey('PageHidden', $resultantRole);
    }

    public function testResultantRoleDualImplicit()
    {
        // arrange
        $roleConfig = $this->roleConfig;
        $roleConfig['admin'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            'PageAdmin'    => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig['example'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            '_hidden'      => true,
            'PageHidden'   => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig = new class($roleConfig) extends RoleConfigurationBase {
            public function __construct($r)
            {
                parent::__construct($r, []);
            }
        };

        // act
        $resultantRole = $roleConfig->getResultantRole(['public', 'loggedIn']);

        // assert
        $this->assertArrayHasKey('PageA', $resultantRole);
        $this->assertArrayHasKey('PageB', $resultantRole);
        $this->assertArrayNotHasKey('PageC', $resultantRole);
        $this->assertArrayNotHasKey('PageAdmin', $resultantRole);
        $this->assertArrayNotHasKey('PageHidden', $resultantRole);
    }

    public function testResultantRoleAllImplicitAndExplicit()
    {
        // arrange
        $roleConfig = $this->roleConfig;
        $roleConfig['admin'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            'PageAdmin'    => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig['example'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            '_hidden'      => true,
            'PageHidden'   => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig = new class($roleConfig) extends RoleConfigurationBase {
            public function __construct($r)
            {
                parent::__construct($r, []);
            }
        };

        // act
        $resultantRole = $roleConfig->getResultantRole(['public', 'loggedIn', 'user', 'admin']);

        // assert
        $this->assertArrayHasKey('PageA', $resultantRole);
        $this->assertArrayHasKey('PageB', $resultantRole);
        $this->assertArrayHasKey('PageC', $resultantRole);
        $this->assertArrayHasKey('PageAdmin', $resultantRole);
        $this->assertArrayNotHasKey('PageHidden', $resultantRole);
    }

    public function testResultantRoleChildRole()
    {
        // arrange
        $roleConfig = $this->roleConfig;
        $roleConfig['admin'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            '_childRoles'  => ['example'],
            'PageAdmin'    => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig['example'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            '_hidden'      => true,
            'PageHidden'   => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig = new class($roleConfig) extends RoleConfigurationBase {
            public function __construct($r)
            {
                parent::__construct($r, []);
            }
        };

        // act
        $resultantRole = $roleConfig->getResultantRole(['admin']);

        // assert
        $this->assertArrayNotHasKey('PageA', $resultantRole);
        $this->assertArrayNotHasKey('PageB', $resultantRole);
        $this->assertArrayNotHasKey('PageC', $resultantRole);
        $this->assertArrayHasKey('PageAdmin', $resultantRole);
        $this->assertArrayHasKey('PageHidden', $resultantRole);
    }

    public function testResultantRoleDeepChildRole()
    {
        // arrange
        $roleConfig = $this->roleConfig;
        $roleConfig['admin'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            '_childRoles'  => ['example'],
            'PageAdmin'    => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig['example'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            '_hidden'      => true,
            'PageHidden'   => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig['user']['_childRoles'] = ['admin'];
        $roleConfig['loggedIn']['_childRoles'] = ['user'];

        $roleConfig = new class($roleConfig) extends RoleConfigurationBase {
            public function __construct($r)
            {
                parent::__construct($r, []);
            }
        };

        // act
        $resultantRole = $roleConfig->getResultantRole(['loggedIn']);

        // assert
        $this->assertArrayNotHasKey('PageA', $resultantRole);
        $this->assertArrayHasKey('PageB', $resultantRole);
        $this->assertArrayHasKey('PageC', $resultantRole);
        $this->assertArrayHasKey('PageAdmin', $resultantRole);
        $this->assertArrayHasKey('PageHidden', $resultantRole);
    }

    public function testResultantRoleChildMultipleInheritance()
    {
        // arrange
        $roleConfig = $this->roleConfig;
        $roleConfig['admin'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            '_childRoles'  => ['example'],
            'PageAdmin'    => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig['example'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            '_hidden'      => true,
            'PageHidden'   => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig['user']['_childRoles'] = ['admin', 'example'];
        $roleConfig['loggedIn']['_childRoles'] = ['user', 'admin', 'example'];

        $roleConfig = new class($roleConfig) extends RoleConfigurationBase {
            public function __construct($r)
            {
                parent::__construct($r, []);
            }
        };

        // act
        $resultantRole = $roleConfig->getResultantRole(['loggedIn']);

        // assert
        $this->assertArrayNotHasKey('PageA', $resultantRole);
        $this->assertArrayHasKey('PageB', $resultantRole);
        $this->assertArrayHasKey('PageC', $resultantRole);
        $this->assertArrayHasKey('PageAdmin', $resultantRole);
        $this->assertArrayHasKey('PageHidden', $resultantRole);
    }

    public function testResultantRoleWithMissingRole()
    {
        // arrange
        $roleConfig = $this->roleConfig;
        $roleConfig['admin'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            'PageAdmin'    => [
                'main' => RoleConfigurationBase::ACCESS_ALLOW,
            ],
        ];

        $roleConfig = new class($roleConfig) extends RoleConfigurationBase {
            public function __construct($r)
            {
                parent::__construct($r, []);
            }
        };

        // act
        $resultantRole = $roleConfig->getResultantRole(['loggedIn', 'example']);

        // assert
        $this->assertArrayNotHasKey('PageA', $resultantRole);
        $this->assertArrayHasKey('PageB', $resultantRole);
        $this->assertArrayNotHasKey('PageC', $resultantRole);
        $this->assertArrayNotHasKey('PageAdmin', $resultantRole);
        $this->assertArrayNotHasKey('PageHidden', $resultantRole);
    }

    public function testResultantRoleWithDefaultDeny()
    {
        // arrange
        $roleConfig = $this->roleConfig;
        $roleConfig['admin'] = [
            '_description' => 'example role',
            '_editableBy'  => [],
            'PageDenied'   => [
                'main' => RoleConfigurationBase::ACCESS_DENY,
            ],
            'PageDefault'  => [
                'main' => RoleConfigurationBase::ACCESS_DEFAULT,
            ],
        ];

        $roleConfig['user']['PageDenied'] = [
            'main' => RoleConfigurationBase::ACCESS_ALLOW,
        ];

        $roleConfig = new class($roleConfig) extends RoleConfigurationBase {
            public function __construct($r)
            {
                parent::__construct($r, []);
            }
        };

        // act
        $resultantRole = $roleConfig->getResultantRole(['user', 'admin']);

        // assert
        $this->assertArrayHasKey('PageDenied', $resultantRole);
        $this->assertArrayHasKey('PageDefault', $resultantRole);

        // a deny action should override an allow action
        $this->assertEquals(RoleConfigurationBase::ACCESS_DENY, $resultantRole['PageDenied']['main']);

        // default actions by definition do nothing.
        $this->assertArrayNotHasKey('main', $resultantRole['PageDefault']);

        // order of roles shouldn't matter
        $reversedRole = $roleConfig->getResultantRole(['admin', 'user']);
        $this->assertEquals(RoleConfigurationBase::ACCESS_DENY, $reversedRole['PageDenied']['main']);
    }

    public function testResultantRoleWithMultipleActions()
    {
        // arrange
        $roleConfig = $this->roleConfig;

        $roleConfig['user']['PageA'] = [
            'create' => RoleConfigurationBase::ACCESS_ALLOW,
            'edit' => RoleConfigurationBase::ACCESS_ALLOW,
            'delete' => RoleConfigurationBase::ACCESS_ALLOW,
        ];

        $roleConfig = new class($roleConfig) extends RoleConfigurationBase {
            public function __construct($r)
            {
                parent::__construct($r, []);
            }
        };

        // act
        $resultantRole = $roleConfig->getResultantRole(['public', 'user']);

        // assert
        $this->assertArrayHasKey('PageA', $resultantRole);

        $this->assertEquals(RoleConfigurationBase::ACCESS_ALLOW, $resultantRole['PageA']['main']);
        $this->assertEquals(RoleConfigurationBase::ACCESS_ALLOW, $resultantRole['PageA']['create']);
        $this->assertEquals(RoleConfigurationBase::ACCESS_ALLOW, $resultantRole['PageA']['edit']);
        $this->assertEquals(RoleConfigurationBase::ACCESS_ALLOW, $resultantRole['PageA']['delete']);
    }
}
