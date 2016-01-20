<?php
namespace Waca;

abstract class PageBase
{
	/**
	 * @var array The callable route to be taken.
	 */
	private $route = null;
	private $routeName = "main";

	/**
	 * Sets the route the request will take
	 *
	 * @param $routeName string
	 * @throws \Exception
	 * @category Security-Critical
	 */
	public final function setRoute($routeName)
	{
		// Test the new route is callable before adopting it.
		$proposedRoute = array($this, $routeName);
		if (!is_callable($proposedRoute)) {
			throw new \Exception("Proposed route '$routeName' is not callable.");
		}

		// Adopt the new route
		$this->route = $proposedRoute;
		$this->routeName = $routeName;
	}

	/**
	 * Runs the page code
	 *
	 * @throws \Exception
	 * @category Security-Critical
	 */
	public final function execute()
	{
		if ($this->route === null) {
			throw new \Exception("Request is unrouted.");
		}

		if ($this->getSecurityConfiguration()->allows(User::getCurrent())) {
			call_user_func($this->route);
		}
		else {
			// TODO: Headers etc
			throw new \Exception("403 error, security config disallows");
		}
	}

	/**
	 * Main function for this page, when no specific actions are called.
	 */
	protected abstract function main();

	/**
	 * Sets up the security for this page.
	 *
	 * @return SecurityConfiguration Mapping of
	 * @category Security-Critical
	 */
	protected function getSecurityConfiguration()
	{
		return new SecurityConfiguration();
	}
}