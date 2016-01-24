<?php
namespace Waca;

use Waca\Pages\Page404;
use Waca\Pages\PageLogin;
use Waca\Pages\PageLogout;
use Waca\Pages\PageMain;
use Waca\Pages\PageSearch;
use Waca\Pages\PageUserManagement;

/**
 * Request router
 * @package Waca
 * @category Security-Critical
 */
class RequestRouter
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
	 *     - If a page is defined and requested, and an action NOT defined and requested, go to Page404 and it's main() method.
	 *     - If a page is NOT defined and requested, go to Page404 and it's main() method.
	 *
	 *     - Query parameters are ignored.
	 *
	 * The key point here is request routing with validation that this is allowed, before we start hitting the filesystem
	 * through the AutoLoader, and opening random files. Also, so that we validate the action requested before we start
	 * calling random methods through the web UI.
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
	 *
	 * @var array
	 */
	private $routeMap = array(
		"logout" =>
			array(
				"class"   => PageLogout::class,
				"actions" => array()
			),
		"login" =>
			array(
				"class"   => PageLogin::class,
				"actions" => array()
			),
		"search" =>
			array(
				"class"   => PageSearch::class,
				"actions" => array()
			),
		"userManagement" =>
			array(
				"class"   => PageUserManagement::class,
				"actions" => array(
					// "approve",
					// "decline",
					// "rename",
					// "edit",
					"suspend",
					// "promote",
					// "demote",
				)
			),
	);

	/**
	 * @return PageBase
	 * @throws \Exception
	 * @category Security-Critical
	 */
	public function route()
	{
		$pathInfo = WebRequest::pathInfo();

		// set up the default action
		$action = "main";

		if (count($pathInfo) === 0) {
			// No pathInfo, so no page to load. Load the main page.
			$pageClass = PageMain::class;
		}
		elseif (count($pathInfo) === 1) {
			// Exactly one path info segment, it's got to be a page.
			$classSegment = $pathInfo[0];

			if (array_key_exists($classSegment, $this->routeMap)) {
				// Route exists, but we don't have an action in path info, so default to main.
				$pageClass = $this->routeMap[$classSegment]['class'];
				$action = "main";
			}
			else {
				// Doesn't exist in map. Fall back to 404
				$pageClass = Page404::class;
				$action = "main";
			}
		}
		else {
			// Multiple path info segments.
			// TODO: account for sub-levels of pages.
			// For now, assume [0] == class & [1] == action

			$classSegment = $pathInfo[0];
			$requestedAction = $pathInfo[1];

			if (array_key_exists($classSegment, $this->routeMap)) {
				// Route exists, but we don't have an action in path info, so default to main.

				if (isset($this->routeMap[$classSegment]['actions'])
					&& array_search($requestedAction, $this->routeMap[$classSegment]['actions']) !== false
				) {
					// Action exists in allowed action list. Allow both the page and the action
					$pageClass = $this->routeMap[$classSegment]['class'];
					$action = $requestedAction;
				}
				else {
					// Valid page, invalid action. 404 our way out.
					$pageClass = Page404::class;
					$action = "main";
				}
			}
			else {
				// Class doesn't exist in map. Fall back to 404
				$pageClass = Page404::class;
				$action = "main";
			}
		}

		/** @var PageBase $page */
		$page = new $pageClass();

		// Dynamic creation, so we've got to be careful here. We can't use built-in language type protection, so
		// let's use our own.
		if (!($page instanceof PageBase)) {
			throw new \Exception("Expected a page, but this is not a page.");
		}

		// OK, I'm happy at this point that we know we're running a page, and we know it's probably what we want if it
		// inherits PageBase and has been created from the routing map.
		$page->setRoute($action);
		return $page;
	}
}