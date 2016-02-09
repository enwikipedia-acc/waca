<?php

namespace Waca\Pages\RequestAction;

use EmailTemplate;
use Exception;
use Logger;
use Notification;
use PdoDatabase;
use Request;
use SessionAlert;
use User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class PageCloseRequest extends RequestActionBase
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

	protected function main() {
		$this->processClose();
	}

	/**
	 * Main function for this page, when no specific actions are called.
	 * @throws ApplicationLogicException
	 */
	protected final function processClose()
	{
		$this->checkPosted();
		$database = gGetDb();

		$currentUser = User::getCurrent();
		$template = $this->getTemplate($database);
		$request = $this->getRequest($database);

		if ($request->getStatus() === 'Closed') {
			throw new ApplicationLogicException('Request is already closed');
		}

		if ($this->checkEmailAlreadySent($request, $template)) {
			return;
		}

		if ($this->checkReserveOverride($request, $template, $currentUser, $database)) {
			return;
		}

		if ($this->checkAccountCreated($request, $template)) {
			return;
		}

		// I think we're good here...
		$request->setStatus('Closed');
		$request->setReserved(0);

		Logger::closeRequest(gGetDb(), $request, $template->getId(), null);
		Notification::requestClosed($request, $template->getName());
		SessionAlert::success("Request {$request->getId()} has been closed");

		$this->sendMail($request, $template, $currentUser);

		$request->updateChecksum();
		$request->save();

		$this->redirect();
	}

	/**
	 * @param $database
	 *
	 * @return EmailTemplate
	 * @throws ApplicationLogicException
	 */
	protected function getTemplate($database)
	{
		$templateId = WebRequest::postInt('template');
		if ($templateId === null) {
			throw new ApplicationLogicException('No template specified');
		}

		/** @var EmailTemplate $template */
		$template = EmailTemplate::getById($templateId, $database);
		if ($template === false || !$template->getActive()) {
			throw new ApplicationLogicException('Invalid or inactive template specified');
		}

		return $template;
	}

	/**
	 * @param Request       $request
	 * @param EmailTemplate $template
	 *
	 * @return bool
	 */
	protected function checkEmailAlreadySent(Request $request, EmailTemplate $template)
	{
		if ($request->getEmailSent() == "1" && !WebRequest::postBoolean('emailSentOverride')) {
			$this->showConfirmation($request, $template, 'close-confirmations/email-sent.tpl');
			return true;
		}

		return false;
	}

	/**
	 * @param Request       $request
	 * @param EmailTemplate $template
	 * @param User          $currentUser
	 *
	 * @param PdoDatabase   $database
	 *
	 * @return bool
	 */
	private function checkReserveOverride(Request $request, EmailTemplate $template, User $currentUser, PdoDatabase $database)
	{
		$reservationId = $request->getReserved();

		if ($reservationId !== 0 && $reservationId !== null) {
			if (!WebRequest::postBoolean('reserveOverride')) {
				if ($currentUser->getId() !== $reservationId) {
					$this->assign('reserveUser', User::getById($reservationId, $database)->getUsername());
					$this->showConfirmation($request, $template, 'close-confirmations/reserve-override.tpl');
					return true;
				}
			}
		}

		return false;
	}

	protected function checkAccountCreated(Request $request, EmailTemplate $template)
	{
		if ($template->getDefaultAction() === EmailTemplate::CREATED && !WebRequest::postBoolean('createOverride')) {
			$url = $this->getSiteConfiguration()->getMediawikiWebServiceEndpoint()
				. '?action=query&list=users&format=php&ususers='
				. urlencode($request->getName());

			$content = $this->getHttpHelper()->get($url);
			$apiResult = unserialize($content);
			$exists = !isset($apiResult['query']['users']['0']['missing']);

			if (!$exists) {
				$this->showConfirmation($request, $template, 'close-confirmations/account-created.tpl');
				return true;
			}
		}

		return false;
	}

	protected function sendMail(Request $request, EmailTemplate $template, User $currentUser)
	{
		$helper = $this->getEmailHelper();

		$emailSig = $currentUser->getEmailSig();
		if($emailSig !== '' || $emailSig !== null){
			$emailSig = "\n\n" . $emailSig;
		}

		$subject = "RE: [ACC #{$request->getId()}] English Wikipedia Account Request";
		$content = $template->getText() . $emailSig;

		$helper->sendMail($request->getEmail(), $subject, $content);

		$request->setEmailSent(1);
	}

	/**
	 * @param Request       $request
	 * @param EmailTemplate $template
	 * @param string        $templateName
	 *
	 * @throws Exception
	 * @return void
	 */
	protected final function showConfirmation(Request $request, EmailTemplate $template, $templateName)
	{
		$this->assign('request', $request->getId());
		$this->assign('template', $template->getId());

		$this->assign('emailSentOverride', WebRequest::postBoolean('emailSentOverride') ? 'true' : 'false');
		$this->assign('reserveOverride', WebRequest::postBoolean('reserveOverride') ? 'true' : 'false');
		$this->assign('createOverride', WebRequest::postBoolean('createOverride') ? 'true' : 'false');

		$this->setTemplate($templateName);
	}
}