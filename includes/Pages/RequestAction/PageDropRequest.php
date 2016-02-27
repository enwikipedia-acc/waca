<?php

namespace Waca\Pages\RequestAction;

use PdoDatabase;
use User;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\Request;

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