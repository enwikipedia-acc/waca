<?php

namespace Waca\Pages\Request;

use BanHelper;
use BootstrapSkin;
use Exception;
use Notification;
use Request;
use SessionAlert;
use Waca\Tasks\PublicInterfacePageBase;
use Waca\Validation\RequestValidationHelper;
use Waca\Validation\ValidationError;
use Waca\WebRequest;

class PageRequestAccount extends PublicInterfacePageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 * @return void
	 */
	protected function main()
	{
		// dual mode page
		if (WebRequest::wasPosted()) {
			$request = $this->createNewRequest();

			$validationErrors = $this->validateRequest($request);

			if (count($validationErrors) > 0) {
				foreach ($validationErrors as $validationError) {
					SessionAlert::error($validationError->getErrorMessage());
				}

				// Validation error, bomb out early.
				$this->redirect();

				return;
			}

			// actually save the request to the database
			if ($this->getSiteConfiguration()->getEmailConfirmationEnabled()) {
				$this->saveAsEmailConfirmation($request);
			}
			else {
				$this->saveWithoutEmailConfirmation($request);
			}
		}
		else {
			$this->setTemplate('request/request-form.tpl');
		}
	}

	/**
	 * @return Request
	 */
	protected function createNewRequest()
	{
		$request = new Request();
		$request->setDatabase($this->getDatabase());

		$request->setName(WebRequest::postString('name'));
		$request->setEmail(WebRequest::postEmail('email'));
		$request->setComment(WebRequest::postString('comments'));

		$request->setIp(WebRequest::remoteAddress());
		$request->setForwardedIp(WebRequest::forwardedAddress());

		$request->setUserAgent(WebRequest::userAgent());

		return $request;
	}

	/**
	 * @param Request $request
	 *
	 * @return ValidationError[]
	 */
	protected function validateRequest($request)
	{
		$validationHelper = new RequestValidationHelper(
			new BanHelper(),
			$request,
			WebRequest::postEmail('emailconfirm'));

		// These are arrays of ValidationError.
		$nameValidation = $validationHelper->validateName();
		$emailValidation = $validationHelper->validateEmail();
		$otherValidation = $validationHelper->validateOther();

		$validationErrors = array_merge($nameValidation, $emailValidation, $otherValidation);

		return $validationErrors;
	}

	/**
	 * @param Request $request
	 *
	 * @throws Exception
	 */
	protected function saveAsEmailConfirmation(Request $request)
	{
		$request->generateEmailConfirmationHash();
		$request->save();

		$trustedIp = $this->getXffTrustProvider()->getTrustedClientIp(
			$request->getIp(),
			$request->getForwardedIp());

		$this->assign("ip", $trustedIp);
		$this->assign("id", $request->getId());
		$this->assign("hash", $request->getEmailConfirm());

		// Sends the confirmation email to the user.
		$this->getEmailHelper()->sendMail(
			$request->getEmail(),
			"[ACC #{$request->getId()}] English Wikipedia Account Request",
			$this->fetchTemplate('request/confirmation-mail.tpl'));

		$this->redirect('emailConfirmationRequired');
	}

	/**
	 * @param Request $request
	 *
	 * @throws Exception
	 */
	protected function saveWithoutEmailConfirmation(Request $request)
	{
		$request->setEmailConfirm(0); // Since it can't be null @todo fixme
		$request->save();

		Notification::requestReceived($request);
		BootstrapSkin::displayPublicFooter();

		$this->redirect('requestSubmitted');
	}
}