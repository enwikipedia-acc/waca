<?php

namespace Waca\Pages\RequestAction;

use Exception;
use Logger;
use Notification;
use SessionAlert;
use User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class PageSendToUser extends RequestActionBase
{
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
		return SecurityConfiguration::internalPage();
	}

	/**
	 * Main function for this page, when no specific actions are called.
	 * @throws ApplicationLogicException
	 * @throws Exception
	 */
	protected function main()
	{
		$this->checkPosted();
		$database = gGetDb();
		$request = $this->getRequest($database);

		if ($request->getReserved() !== User::getCurrent()->getId()) {
			throw new ApplicationLogicException('You don\'t have this request reserved!');
		}

		$username = WebRequest::postString('user');
		if ($username === null) {
			throw new ApplicationLogicException('User must be specified');
		}

		$user = User::getByUsername($username, $database);
		if ($user === false) {
			throw new ApplicationLogicException('User not found');
		}

		if (!$user->isUser() && !$user->isAdmin()) {
			throw new ApplicationLogicException('User is currently not active on the tool');
		}

		$request->setReserved($user->getId());
		$request->save();

		Logger::sendReservation($database, $request, $user);
		Notification::requestReservationSent($request, $user);
		SessionAlert::success("Reservation sent successfully");

		$this->redirect('viewRequest', null, array('id' => $request->getId()));
	}
}