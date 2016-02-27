<?php

namespace Waca\Pages\Request;

use Logger;
use Request;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Tasks\PublicInterfacePageBase;
use Waca\WebRequest;

class PageConfirmEmail extends PublicInterfacePageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 * @throws ApplicationLogicException
	 * @return void
	 */
	protected function main()
	{
		$id = WebRequest::getInt('id');
		$si = WebRequest::getString('si');

		if ($id === null || $si === null) {
			throw new ApplicationLogicException('Link incomplete - please double check the link you received.');
		}

		/** @var Request|false $request */
		$request = Request::getById($id, $this->getDatabase());

		if ($request === false) {
			throw new ApplicationLogicException('Request not found');
		}

		if ($request->getEmailConfirm() === 'Confirmed') {
			// request has already been confirmed. Bomb out silently.
			$this->redirect('requestSubmitted');

			return;
		}

		if ($request->getEmailConfirm() === $si) {
			$request->setEmailConfirm('Confirmed');
		}
		else {
			throw new ApplicationLogicException('The confirmation value does not appear to match the expected value');
		}

		$request->save();

		Logger::emailConfirmed($this->getDatabase(), $request);
		$this->getNotificationHelper()->requestReceived($request);

		$this->redirect('requestSubmitted');
	}
}