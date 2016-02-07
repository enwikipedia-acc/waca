<?php
namespace Waca;

use Exception;
use Pages\PageViewRequest;
use Waca\Pages\Page404;
use Waca\Pages\PageBan;
use Waca\Pages\RequestAction\PageBreakReservation;
use Waca\Pages\PageEmailManagement;
use Waca\Pages\PageForgotPassword;
use Waca\Pages\PageInterfaceManagement;
use Waca\Pages\PageLog;
use Waca\Pages\PageLogin;
use Waca\Pages\PageLogout;
use Waca\Pages\PageMain;
use Waca\Pages\PagePreferences;
use Waca\Pages\RequestAction\PageDeferRequest;
use Waca\Pages\RequestAction\PageReservation;
use Waca\Pages\PageSearch;
use Waca\Pages\PageTeam;
use Waca\Pages\PageUserManagement;
use Waca\Pages\PageWelcomeTemplateManagement;
use Waca\Pages\Statistics\StatsFastCloses;
use Waca\Pages\Statistics\StatsIdUsers;
use Waca\Pages\Statistics\StatsInactiveUsers;
use Waca\Pages\Statistics\StatsMain;
use Waca\Pages\Statistics\StatsMonthlyStats;
use Waca\Pages\Statistics\StatsReservedRequests;
use Waca\Pages\Statistics\StatsTemplateStats;
use Waca\Pages\Statistics\StatsTopCreators;
use Waca\Pages\Statistics\StatsUsers;

/**
 * Request router
 * @package  Waca
 * @category Security-Critical
 */
final class RequestRouter
{
	/**
	 * This is the core routing table for the application. The basic idea is:
	 *
	 *      array(
	 *          "foo" =>
	 *              array(
	 *                  "class"   => PageFoo::class,
	 *                  "actions" => array("bar", "other")
	 *              ),
	 * );
	 *
	 * Things to note:
	 *     - If no page is requested, we go to PageMain. PageMain can't have actions defined.
	 *
	 *     - If a page is defined and requested, but no action is requested, go to that page's main() method
	 *     - If a page is defined and requested, and an action is defined and requested, go to that action's method.
	 *     - If a page is defined and requested, and an action NOT defined and requested, go to Page404 and it's main()
	 *       method.
	 *     - If a page is NOT defined and requested, go to Page404 and it's main() method.
	 *
	 *     - Query parameters are ignored.
	 *
	 * The key point here is request routing with validation that this is allowed, before we start hitting the
	 * filesystem through the AutoLoader, and opening random files. Also, so that we validate the action requested
	 * before we start calling random methods through the web UI.
	 *
	 * Examples:
	 * /internal.php                => returns instance of PageMain, routed to main()
	 * /internal.php?query          => returns instance of PageMain, routed to main()
	 * /internal.php/foo            => returns instance of PageFoo, routed to main()
	 * /internal.php/foo?query      => returns instance of PageFoo, routed to main()
	 * /internal.php/foo/bar        => returns instance of PageFoo, routed to bar()
	 * /internal.php/foo/bar?query  => returns instance of PageFoo, routed to bar()
	 * /internal.php/foo/baz        => returns instance of Page404, routed to main()
	 * /internal.php/foo/baz?query  => returns instance of Page404, routed to main()
	 * /internal.php/bar            => returns instance of Page404, routed to main()
	 * /internal.php/bar?query      => returns instance of Page404, routed to main()
	 * /internal.php/bar/baz        => returns instance of Page404, routed to main()
	 * /internal.php/bar/baz?query  => returns instance of Page404, routed to main()
	 *
	 * Take care when changing this - a lot of places rely on the array key for redirects and other links. If you need
	 * to change the key, then you'll likely have to update a lot of files.
	 *
	 * @var array
	 */
	private $routeMap = array(
		'logout'                      =>
			array(
				'class'   => PageLogout::class,
				'actions' => array(),
			),
		'login'                       =>
			array(
				'class'   => PageLogin::class,
				'actions' => array(),
			),
		'forgotPassword'              =>
			array(
				'class'   => PageForgotPassword::class,
				'actions' => array('reset'),
			),
		'search'                      =>
			array(
				'class'   => PageSearch::class,
				'actions' => array(),
			),
		'logs'                        =>
			array(
				'class'   => PageLog::class,
				'actions' => array(),
			),
		'bans'                        =>
			array(
				'class'   => PageBan::class,
				'actions' => array('set', 'remove'),
			),
		'userManagement'              =>
			array(
				'class'   => PageUserManagement::class,
				'actions' => array(
					'approve',
					'decline',
					'rename',
					'editUser',
					'suspend',
					'promote',
					'demote',
				),
			),
		'siteNotice'                  =>
			array(
				'class'   => PageInterfaceManagement::class,
				'actions' => array(),
			),
		'preferences'                 =>
			array(
				'class'   => PagePreferences::class,
				'actions' => array('changePassword'),
			),
		'welcomeTemplates'            =>
			array(
				'class'   => PageWelcomeTemplateManagement::class,
				'actions' => array('select', 'edit', 'delete', 'add', 'view'),
			),
		'statistics'                  =>
			array(
				'class'   => StatsMain::class,
				'actions' => array(),
			),
		'statistics/fastCloses'       =>
			array(
				'class'   => StatsFastCloses::class,
				'actions' => array(),
			),
		'statistics/idUsers'          =>
			array(
				'class'   => StatsIdUsers::class,
				'actions' => array(),
			),
		'statistics/inactiveUsers'    =>
			array(
				'class'   => StatsInactiveUsers::class,
				'actions' => array(),
			),
		'statistics/monthlyStats'     =>
			array(
				'class'   => StatsMonthlyStats::class,
				'actions' => array(),
			),
		'statistics/reservedRequests' =>
			array(
				'class'   => StatsReservedRequests::class,
				'actions' => array(),
			),
		'statistics/templateStats'    =>
			array(
				'class'   => StatsTemplateStats::class,
				'actions' => array(),
			),
		'statistics/topCreators'      =>
			array(
				'class'   => StatsTopCreators::class,
				'actions' => array(),
			),
		'statistics/users'            =>
			array(
				'class'   => StatsUsers::class,
				'actions' => array('detail'),
			),
		'viewRequest'                 =>
			array(
				'class'   => PageViewRequest::class,
				'actions' => array(),
			),
		'team'                        =>
			array(
				'class'   => PageTeam::class,
				'actions' => array(),
			),
		'viewRequest/reserve'         =>
			array(
				'class'   => PageReservation::class,
				'actions' => array(),
			),
		'viewRequest/breakReserve'    =>
			array(
				'class'   => PageBreakReservation::class,
				'actions' => array(),
			),
		'viewRequest/defer'           =>
			array(
				'class'   => PageDeferRequest::class,
				'actions' => array(),
			),
		'emailManagement'             =>
			array(
				'class'   => PageEmailManagement::class,
				'actions' => array('create', 'edit', 'view'),
			),

	);

	/**
	 * @return PageBase
	 * @throws Exception
	 */
	public function route()
	{
		$pathInfo = WebRequest::pathInfo();

		list($pageClass, $action) = $this->getRouteFromPath($pathInfo);

		/** @var PageBase $page */
		$page = new $pageClass();

		// Dynamic creation, so we've got to be careful here. We can't use built-in language type protection, so
		// let's use our own.
		if (!($page instanceof PageBase)) {
			throw new Exception('Expected a page, but this is not a page.');
		}

		// OK, I'm happy at this point that we know we're running a page, and we know it's probably what we want if it
		// inherits PageBase and has been created from the routing map.
		$page->setRoute($action);

		return $page;
	}

	/**
	 * @param $pathInfo
	 *
	 * @return array
	 */
	public function getRouteFromPath($pathInfo)
	{
		if (count($pathInfo) === 0) {
			// No pathInfo, so no page to load. Load the main page.
			$pageClass = PageMain::class;
			$action = "main";

			return array($pageClass, $action);
		}
		elseif (count($pathInfo) === 1) {
			// Exactly one path info segment, it's got to be a page.
			$classSegment = $pathInfo[0];

			return $this->routeSinglePathSegment($classSegment);
		}

		// OK, we have two or more segments now.
		if (count($pathInfo) > 2) {
			// Let's handle more than two, and collapse it down into two.
			$requestedAction = array_pop($pathInfo);
			$classSegment = implode('/', $pathInfo);
		}
		else {
			// Two path info segments.
			$classSegment = $pathInfo[0];
			$requestedAction = $pathInfo[1];
		}

		$routeMap = $this->routePathSegments($classSegment, $requestedAction);

		if ($routeMap[0] === Page404::class) {
			$routeMap = $this->routeSinglePathSegment($classSegment . '/' . $requestedAction);
		}

		return $routeMap;
	}

	/**
	 * @param $classSegment
	 *
	 * @return array
	 */
	private function routeSinglePathSegment($classSegment)
	{
		if (array_key_exists($classSegment, $this->routeMap)) {
			// Route exists, but we don't have an action in path info, so default to main.
			$pageClass = $this->routeMap[$classSegment]['class'];
			$action = 'main';

			return array($pageClass, $action);
		}
		else {
			// Doesn't exist in map. Fall back to 404
			$pageClass = Page404::class;
			$action = "main";

			return array($pageClass, $action);
		}
	}

	/**
	 * @param $classSegment
	 * @param $requestedAction
	 *
	 * @return array
	 */
	private function routePathSegments($classSegment, $requestedAction)
	{
		if (array_key_exists($classSegment, $this->routeMap)) {
			// Route exists, but we don't have an action in path info, so default to main.

			if (isset($this->routeMap[$classSegment]['actions'])
				&& array_search($requestedAction, $this->routeMap[$classSegment]['actions']) !== false
			) {
				// Action exists in allowed action list. Allow both the page and the action
				$pageClass = $this->routeMap[$classSegment]['class'];
				$action = $requestedAction;

				return array($pageClass, $action);
			}
			else {
				// Valid page, invalid action. 404 our way out.
				$pageClass = Page404::class;
				$action = 'main';

				return array($pageClass, $action);
			}
		}
		else {
			// Class doesn't exist in map. Fall back to 404
			$pageClass = Page404::class;
			$action = 'main';

			return array($pageClass, $action);
		}
	}
}