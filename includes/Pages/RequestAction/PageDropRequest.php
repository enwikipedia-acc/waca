<?php

namespace Waca\Pages\RequestAction;

use EmailTemplate;
use PdoDatabase;
use Request;
use User;

class PageDropRequest extends PageCloseRequest
{
	protected function getTemplate(PdoDatabase $database)
	{
		return EmailTemplate::getDroppedTemplate();
	}

	protected function checkEmailAlreadySent(Request $request, EmailTemplate $template)
	{
		return false;
	}

	protected function checkAccountCreated(Request $request, EmailTemplate $template)
	{
		return false;
	}

	protected function sendMail(Request $request, EmailTemplate $template, User $currentUser, $ccMailingList)
	{
	}
}