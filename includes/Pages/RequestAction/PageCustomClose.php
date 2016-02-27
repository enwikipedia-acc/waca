<?php

namespace Waca\Pages\RequestAction;

use Exception;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\PdoDatabase;
use Waca\SessionAlert;
use Waca\WebRequest;

class PageCustomClose extends PageCloseRequest
{
	protected function main()
	{
		$database = $this->getDatabase();

		$request = $this->getRequest($database);
		$currentUser = User::getCurrent($this->getDatabase());

		if ($request->getStatus() === 'Closed') {
			throw new ApplicationLogicException('Request is already closed');
		}

		// @todo: checks for reservation, already created, etc - do this as checkboxes on custom page if detected to avoid
		//        the user having to click through prompts, unless it's somethig that's happened very recently.

		// Dual-mode page
		if (WebRequest::wasPosted()) {
			$this->doCustomClose($currentUser, $request, $database);

			$this->redirect();
		}
		else {
			$this->showCustomCloseForm($database, $request);
		}
	}

	/**
	 * @param $database
	 *
	 * @return Request
	 * @throws ApplicationLogicException
	 */
	protected function getRequest(PdoDatabase $database)
	{
		$requestId = WebRequest::getInt('request');
		if ($requestId === null) {
			throw new ApplicationLogicException('Request ID not found');
		}

		/** @var Request $request */
		$request = Request::getById($requestId, $database);

		if ($request === false) {
			throw new ApplicationLogicException('Request not found');
		}

		return $request;
	}

	/**
	 * @param PdoDatabase $database
	 *
	 * @return EmailTemplate|null
	 */
	protected function getTemplate(PdoDatabase $database)
	{
		$templateId = WebRequest::getInt('template');
		if ($templateId === null) {
			return null;
		}

		/** @var EmailTemplate $template */
		$template = EmailTemplate::getById($templateId, $database);
		if ($template === false || !$template->getActive()) {
			return null;
		}

		return $template;
	}

	/**
	 * @param $database
	 * @param $request
	 *
	 * @throws Exception
	 */
	protected function showCustomCloseForm(PdoDatabase $database, Request $request)
	{
		$template = $this->getTemplate($database);

		$this->assign('defaultAction', '');
		$this->assign('preloadText', '');
		$this->assign('preloadTitle', '');

		if ($template !== null) {
			$this->assign('defaultAction', $template->getDefaultAction());
			$this->assign('preloadText', $template->getText());
			$this->assign('preloadTitle', $template->getName());
		}

		$this->assign('requeststates', $this->getSiteConfiguration()->getRequestStates());

		$this->assign('requestId', $request->getIp());

		// @todo request info on form - we need to do this slightly better than it was before.
		// $this->assign("request", $request);

		$trustedIp = $this->getXffTrustProvider()->getTrustedClientIp($request->getIp(), $request->getForwardedIp());
		$this->assign('iplocation', $this->getLocationProvider()->getIpLocation($trustedIp));

		$this->setTemplate('custom-close.tpl');
	}

	/**
	 * @param string      $action
	 * @param Request     $request
	 * @param PdoDatabase $database
	 * @param string      $messageBody
	 */
	protected function closeRequest($action, Request $request, PdoDatabase $database, $messageBody)
	{
		$request->setStatus('Closed');

		if ($action == EmailTemplate::CREATED) {
			$logCloseType = 'custom-y';
			$notificationCloseType = "Custom, Created";
		}
		else {
			$logCloseType = 'custom-n';
			$notificationCloseType = "Custom, Not Created";
		}

		Logger::closeRequest($database, $request, $logCloseType, $messageBody);
		$this->getNotificationHelper()->requestClosed($request, $notificationCloseType);

		$requestName = htmlentities($request->getName(), ENT_COMPAT, 'UTF-8');
		SessionAlert::success("Request {$request->getId()} ({$requestName}) marked as 'Done'.");
	}

	/**
	 * @param Request     $request
	 * @param string      $action
	 * @param array       $availableRequestStates
	 * @param PdoDatabase $database
	 * @param string      $messageBody
	 */
	protected function deferRequest($request, $action, $availableRequestStates, $database, $messageBody)
	{
		$request->setStatus($action);

		$detolog = $availableRequestStates[$action]['defertolog'];
		Logger::sentMail($database, $request, $messageBody);
		Logger::deferRequest($database, $request, $detolog);

		$this->getNotificationHelper()->requestDeferredWithMail($request);

		$deto = $availableRequestStates[$action]['deferto'];
		SessionAlert::success("Request {$request->getId()} deferred to $deto, sending an email.");
	}

	/**
	 * @param User        $currentUser
	 * @param Request     $request
	 * @param PdoDatabase $database
	 *
	 * @throws ApplicationLogicException
	 */
	protected function doCustomClose(User $currentUser, Request $request, PdoDatabase $database)
	{
		$messageBody = WebRequest::postString('msgbody');
		if ($messageBody === null || trim($messageBody) === '') {
			throw new ApplicationLogicException('Message body cannot be blank');
		}

		$ccMailingList = true;
		if ($currentUser->isAdmin() || $currentUser->isCheckuser()) {
			$ccMailingList = WebRequest::postBoolean('ccMailingList');
		}

		$this->sendMail($request, $messageBody, $currentUser, $ccMailingList);

		$action = WebRequest::postString('action');
		$availableRequestStates = $this->getSiteConfiguration()->getRequestStates();

		if ($action === EmailTemplate::CREATED || $action === EmailTemplate::NOT_CREATED) {
			// Close request
			$this->closeRequest($action, $request, $database, $messageBody);
		}
		else {
			if (array_key_exists($action, $availableRequestStates)) {
				// Defer to other state
				$this->deferRequest($request, $action, $availableRequestStates, $database, $messageBody);
			}
			else {
				// Send mail
				Logger::sentMail($database, $request, $messageBody);
				Logger::unreserve($database, $request);

				$this->getNotificationHelper()->sentMail($request);
				SessionAlert::success("Sent mail to Request {$request->getId()}");
			}
		}

		$request->setReserved(0);
		$request->save();

		$request->updateChecksum();
		$request->save();
	}
}