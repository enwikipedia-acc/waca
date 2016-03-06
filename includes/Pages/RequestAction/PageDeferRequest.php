<?php

namespace Waca\Pages\RequestAction;

use DateTime;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\SecurityConfiguration;
use Waca\SessionAlert;
use Waca\WebRequest;

class PageDeferRequest extends RequestActionBase
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
	 */
	protected function main()
	{
		$this->checkPosted();
		$database = $this->getDatabase();
		$request = $this->getRequest($database);
		$currentUser = User::getCurrent($database);

		$target = WebRequest::postString('target');
		$requestStates = $this->getSiteConfiguration()->getRequestStates();

		if (!array_key_exists($target, $requestStates)) {
			throw new ApplicationLogicException('Defer target not valid');
		}

		if ($request->getStatus() == $target) {
			SessionAlert::warning('This request is already in the specified queue.');
			$this->redirect('viewRequest', null, array('id' => $request->getId()));

			return;
		}

		$closureDate = $request->getClosureDate();
		$date = new DateTime();
		$date->modify("-7 days");
		$oneweek = $date->format("Y-m-d H:i:s");

		if ($request->getStatus() == "Closed" && $closureDate < $oneweek && !$currentUser->isAdmin()) {
			throw new ApplicationLogicException(
				"Only administrators and checkusers can reserve a request that has been closed for over a week.");
		}

		$request->setReserved(0);
		$request->setStatus($target);
		$request->setUpdateVersion(WebRequest::postInt('updateversion'));
		$request->save();

		$deto = $requestStates[$target]['deferto'];
		$detolog = $requestStates[$target]['defertolog'];

		Logger::deferRequest($database, $request, $detolog);

		$this->getNotificationHelper()->requestDeferred($request);
		SessionAlert::success("Request {$request->getId()} deferred to {$deto}");

		$this->redirect();
	}
}