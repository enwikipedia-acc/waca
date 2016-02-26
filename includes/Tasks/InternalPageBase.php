<?php
namespace Waca\Tasks;

use Exception;
use User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\NotIdentifiedException;
use Waca\SecurityConfiguration;

abstract class InternalPageBase extends PageBase
{
	/**
	 * Runs the page code
	 *
	 * @throws Exception
	 * @category Security-Critical
	 */
	final public function execute()
	{
		if ($this->getRouteName() === null) {
			throw new Exception("Request is unrouted.");
		}

		if ($this->getSiteConfiguration() === null) {
			throw new Exception("Page has no configuration!");
		}

		$this->setupPage();

		// Get the current security configuration
		$securityConfiguration = $this->getSecurityConfiguration();
		if ($securityConfiguration === null) {
			// page hasn't been written properly.
			throw new AccessDeniedException();
		}

		$currentUser = User::getCurrent($this->getDatabase());

		// Security barrier.
		//
		// This code essentially doesn't care if the user is logged in or not, as the
		if ($securityConfiguration->allows($currentUser)) {
			// We're allowed to run the page, so let's run it.
			$this->runPage();
		}
		else {
			$this->handleAccessDenied();

			// Send the headers
			foreach ($this->headerQueue as $item) {
				header($item);
			}
		}
	}

	/**
	 * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
	 * the return value from this function.
	 *
	 * If this page even supports actions, you will need to check the route
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	abstract protected function getSecurityConfiguration();

	protected function handleAccessDenied()
	{
		// Not allowed to access this resource.
		// Firstly, let's check if we're even logged in.
		if (User::getCurrent()->isCommunityUser()) {
			// Not logged in, redirect to login page

			// TODO: return to current page? Possibly as a session var?
			$this->redirect("login");

			return;
		}
		else {
			// Decide whether this was a rights failure, or an identification failure.

			if ($this->getSiteConfiguration()->getForceIdentification()
				&& User::getCurrent()->isIdentified() != 1
			) {
				// Not identified
				throw new NotIdentifiedException();
			}
			else {
				// Nope, plain old access denied
				throw new AccessDeniedException();
			}
		}
	}

	/**
	 * Tests the security barrier for a specified action.
	 *
	 * Intended to be used from within templates
	 *
	 * @param string $action
	 *
	 * @return boolean
	 * @category Security-Critical
	 */
	final public function barrierTest($action)
	{
		$tmpRouteName = $this->getRouteName();

		try {
			$this->routeName = $action;
			$allowed = $this->getSecurityConfiguration()->allows(User::getCurrent());
		}
		finally {
			$this->routeName = $tmpRouteName;
		}

		return $allowed;
	}
}