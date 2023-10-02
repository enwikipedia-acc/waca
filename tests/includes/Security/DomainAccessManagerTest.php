<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Security;

use PHPUnit\Framework\TestCase;
use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\Exceptions\DomainSwitchNotAllowedException;
use Waca\Providers\GlobalState\FakeGlobalStateProvider;
use Waca\Providers\GlobalState\IGlobalStateProvider;
use Waca\Security\DomainAccessManager;
use Waca\Security\IUserAccessLoader;
use Waca\WebRequest;

class DomainAccessManagerTest extends TestCase
{
    private User $user;

    private IUserAccessLoader $userAccessLoader;

    private IGlobalStateProvider $stateProvider;

    public function setUp() : void
    {
        $this->user = $this->getMockBuilder(User::class)->getMock();
        $this->userAccessLoader = $this->getMockBuilder(IUserAccessLoader::class)->getMock();

        $this->stateProvider = new FakeGlobalStateProvider();
        WebRequest::setGlobalStateProvider($this->stateProvider);
    }

    public function testGetAllowedDomainsCommunity() {
        // arrange
        $dam = new DomainAccessManager($this->userAccessLoader);

        // act
        $domains = $dam->getAllowedDomains(User::getCommunity());

        // assert
        $this->assertCount(0, $domains);
    }

    public function testGetAllowedDomainsUser() {
        // arrange
        $dam = new DomainAccessManager($this->userAccessLoader);

        $d = $this->getMockBuilder(Domain::class)->getMock();

        $this->userAccessLoader->method('loadDomainsForUser')->willReturn([
            $d
        ]);

        // act
        $domains = $dam->getAllowedDomains($this->user);

        // assert
        $this->assertCount(1, $domains);
        $this->assertEquals($d, $domains[0]);
    }

    public function testSwitchDomain() {
        // arrange
        $dam = new DomainAccessManager($this->userAccessLoader);

        $d1 = $this->getMockBuilder(Domain::class)->getMock();
        $d1->method('getId')->willReturn(1);

        $d2 = $this->getMockBuilder(Domain::class)->getMock();
        $d2->method('getId')->willReturn(2);

        $this->stateProvider->session = ['domainID' => 1];

        $this->userAccessLoader->method('loadDomainsForUser')->willReturn([$d1, $d2]);

        // act
        $dam->switchDomain($this->user, $d2);

        // assert
        $this->assertEquals(2, $this->stateProvider->session['domainID']);
    }

    public function testSwitchDomainNotAllowed() {
        // arrange
        $dam = new DomainAccessManager($this->userAccessLoader);

        $d1 = $this->getMockBuilder(Domain::class)->getMock();
        $d1->method('getId')->willReturn(1);

        $d2 = $this->getMockBuilder(Domain::class)->getMock();
        $d2->method('getId')->willReturn(2);

        $this->stateProvider->session = ['domainID' => 1];

        $this->userAccessLoader->method('loadDomainsForUser')->willReturn([$d1]);

        $exceptionThrown = false;

        // act
        try {
            $dam->switchDomain($this->user, $d2);
        } catch (DomainSwitchNotAllowedException $e) {
            $exceptionThrown = true;
        }

        // assert
        $this->assertEquals(1, $this->stateProvider->session['domainID']);
        $this->assertTrue($exceptionThrown);
    }
}