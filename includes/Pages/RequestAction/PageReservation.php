<?php

namespace Waca\Pages\RequestAction;

use DateTime;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\SecurityConfiguration;
use Waca\SessionAlert;

class PageReservation extends RequestActionBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 * @throws ApplicationLogicException
	 */
	protected function main()
	{
		$this->checkPosted();
		$database = $this->getDatabase();
		$request = $this->getRequest($database);

		$closureDate = $request->getClosureDate();

		$date = new DateTime();
		$date->modify("-7 days");
		$oneweek = $date->format("Y-m-d H:i:s");

		if ($request->getStatus() == "Closed" && $closureDate < $oneweek && !User::getCurrent($database)->isAdmin()) {
			throw new ApplicationLogicException(
				"Only administrators and checkusers can reserve a request that has been closed for over a week.");
		}

		if ($request->getReserved() != 0 && $request->getReserved() != User::getCurrent($database)->getId()) {
			throw new ApplicationLogicException("Request is already reserved!");
		}

		if ($request->getReserved() == 0) {
			// Check the number of requests a user has reserved already
			$doubleReserveCountQuery = $database->prepare("SELECT COUNT(*) FROM request WHERE reserved = :userid;");
			$doubleReserveCountQuery->bindValue(":userid", User::getCurrent($database)->getId());
			$doubleReserveCountQuery->execute();
			$doubleReserveCount = $doubleReserveCountQuery->fetchColumn();
			$doubleReserveCountQuery->closeCursor();

			// User already has at least one reserved.
			if ($doubleReserveCount != 0) {
				SessionAlert::warning("You have multiple requests reserved!");
			}

			$request->setReserved(User::getCurrent($database)->getId());
			$request->save();

			Logger::reserve($database, $request);

			$this->getNotificationHelper()->requestReserved($request);

			SessionAlert::success("Reserved request {$request->getId()}.");
		}

		$this->redirect('viewRequest', null, array('id' => $request->getId()));
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
		return SecurityConfiguration::internalPage();
	}
}