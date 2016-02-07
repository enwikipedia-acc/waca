<?php

namespace Waca\Pages;

use Exception;
use Logger;
use Notification;
use PdoDatabase;
use Request;
use User;
use Waca\Exceptions\AccessDeniedException;
use Waca\Exceptions\ApplicationLogicException;
use Waca\SecurityConfiguration;

class PageBreakReservation extends RequestActionBase
{
	protected function main()
	{
		$this->checkPosted();
		$database = gGetDb();
		$request = $this->getRequest($database);

		if ($request->getReserved() === 0 || $request->getReserved() === null) {
			throw new ApplicationLogicException('Request is not reserved!');
		}

		$currentUser = User::getCurrent();

		if ($currentUser->getId() === $request->getReserved()) {
			$this->doUnreserve($request, $database);
		}
		else {
			// not the same user!
			if ($this->barrierTest('force')) {
				$this->doBreakReserve($request, $database);
			}
			else {
				throw new AccessDeniedException();
			}
		}
	}

	/**
	 * @param Request     $request
	 * @param PdoDatabase $database
	 *
	 * @throws Exception
	 */
	protected function doUnreserve(Request $request, PdoDatabase $database)
	{
		// same user! we allow people to unreserve their own stuff
		$request->setReserved(0);
		$request->save();

		Logger::unreserve($database, $request);
		Notification::requestUnreserved($request);

		// Redirect home!
		$this->redirect();
	}

	/**
	 * @param Request     $request
	 * @param PdoDatabase $database
	 *
	 * @throws Exception
	 */
	protected function doBreakReserve(Request $request, PdoDatabase $database)
	{
		// @todo add a confirmation here

		$request->setReserved(0);
		$request->save();

		Logger::breakReserve($database, $request);
		Notification::requestReserveBroken($request);

		// Redirect home!
		$this->redirect();
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
	protected function getSecurityConfiguration()
	{
		switch ($this->getRouteName()) {
			case 'force':
				// note, this is a virtual route that's only used in barrier tests
				return SecurityConfiguration::adminPage();
			default:
				return SecurityConfiguration::internalPage();
		}
	}
}