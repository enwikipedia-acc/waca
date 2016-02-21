<?php

namespace Waca\Tests;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Waca\Pages\Page404;
use Waca\Pages\PageLogout;
use Waca\Pages\PageMain;
use Waca\Pages\PageUserManagement;
use Waca\Providers\GlobalStateProvider;
use Waca\Router\RequestRouter;
use Waca\WebRequest;

class RequestRouterTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var GlobalStateProvider|PHPUnit_Framework_MockObject_MockObject
	 */
	private $globalState;

	public function setUp()
	{
		$this->globalState = $this->getMockBuilder(GlobalStateProvider::class)->getMock();
		WebRequest::setGlobalStateProvider($this->globalState);
	}

	public function testEmptyPathInfo()
	{
		$router = new RequestRouter();
		list($pageClass, $action) = $router->getRouteFromPath(array());

		$this->assertEquals(PageMain::class, $pageClass);
		$this->assertEquals('main', $action);
	}

	public function testSingleItemPathInfo()
	{
		$router = new RequestRouter();
		list($pageClass, $action) = $router->getRouteFromPath(array('logout'));

		$this->assertEquals(PageLogout::class, $pageClass);
		$this->assertEquals('main', $action);
	}

	public function testSingleItemPathInfoNotFound()
	{
		$router = new RequestRouter();
		list($pageClass, $action) = $router->getRouteFromPath(array('pleaseDontExist'));

		$this->assertEquals(Page404::class, $pageClass);
		$this->assertEquals('main', $action);
	}

	public function testDualItem()
	{
		$router = new RequestRouter();
		list($pageClass, $action) = $router->getRouteFromPath(array('userManagement', 'approve'));

		$this->assertEquals(PageUserManagement::class, $pageClass);
		$this->assertEquals('approve', $action);
	}

	public function testDualItemInvalidAction()
	{
		$router = new RequestRouter();
		list($pageClass, $action) = $router->getRouteFromPath(array('userManagement', 'dontExist'));

		$this->assertEquals(Page404::class, $pageClass);
		$this->assertEquals('main', $action);
	}

	public function testDualItemInvalidPage()
	{
		$router = new RequestRouter();
		list($pageClass, $action) = $router->getRouteFromPath(array('dontExist', 'approve'));

		$this->assertEquals(Page404::class, $pageClass);
		$this->assertEquals('main', $action);
	}

	public function testDualItemInvalidBoth()
	{
		$router = new RequestRouter();
		list($pageClass, $action) = $router->getRouteFromPath(array('dontExist', 'definitelyDontExist'));

		$this->assertEquals(Page404::class, $pageClass);
		$this->assertEquals('main', $action);
	}

	public function testCreatesRoutedPage()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array(
			'PATH_INFO' => '/userManagement/approve',
		));

		$router = new RequestRouter();
		$page = $router->route();

		$this->assertEquals(PageUserManagement::class, get_class($page));
		$this->assertEquals('approve', $page->getRouteName());
	}

	public function testSubPagePathRouting()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array(
			'PATH_INFO' => '/foo/bar',
		));

		$routeMap = array(
			'foo/bar' =>
				array(
					'class'   => PageUserManagement::class,
					'actions' => array('approve'),
				),
		);

		$router = new RequestRouter();

		// set request route using reflection
		$reflector = new ReflectionProperty(RequestRouter::class, 'routeMap');
		$reflector->setAccessible(true);
		$reflector->setValue($router, $routeMap);

		$page = $router->route();

		$this->assertEquals(PageUserManagement::class, get_class($page));
		$this->assertEquals('main', $page->getRouteName());
	}

	public function testSubPagePathRoutingWithAction()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array(
			'PATH_INFO' => '/foo/bar/approve',
		));

		$routeMap = array(
			'foo/bar' =>
				array(
					'class'   => PageUserManagement::class,
					'actions' => array('approve'),
				),
		);

		$router = new RequestRouter();

		// set request route using reflection
		$reflector = new ReflectionProperty(RequestRouter::class, 'routeMap');
		$reflector->setAccessible(true);
		$reflector->setValue($router, $routeMap);

		$page = $router->route();

		$this->assertEquals(PageUserManagement::class, get_class($page));
		$this->assertEquals('approve', $page->getRouteName());
	}

	public function testSubPagePathRoutingStress()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array(
			'PATH_INFO' => '/a/b/c/d/e/f/g/h/i/j/k/l/m/n/o/p/q/r/s/t/u/v/w/x/y/z/approve',
		));

		$routeMap = array(
			'a/b/c/d/e/f/g/h/i/j/k/l/m/n/o/p/q/r/s/t/u/v/w/x/y/z' =>
				array(
					'class'   => PageUserManagement::class,
					'actions' => array('approve'),
				),
		);

		$router = new RequestRouter();

		// set request route using reflection
		$reflector = new ReflectionProperty(RequestRouter::class, 'routeMap');
		$reflector->setAccessible(true);
		$reflector->setValue($router, $routeMap);

		$page = $router->route();

		$this->assertEquals(PageUserManagement::class, get_class($page));
		$this->assertEquals('approve', $page->getRouteName());
	}

	public function testSubPagePathRoutingWithPartialMatch()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array(
			'PATH_INFO' => 'stats/foo',
		));

		$routeMap = array(
			'stats' =>
				array(
					'class'   => PageMain::class,
					'actions' => array(),
				),
			'stats/foo' =>
				array(
					'class'   => PageUserManagement::class,
					'actions' => array(),
				),
		);

		$router = new RequestRouter();

		// set request route using reflection
		$reflector = new ReflectionProperty(RequestRouter::class, 'routeMap');
		$reflector->setAccessible(true);
		$reflector->setValue($router, $routeMap);

		$page = $router->route();

		$this->assertEquals(PageUserManagement::class, get_class($page));
		$this->assertEquals('main', $page->getRouteName());
	}
}