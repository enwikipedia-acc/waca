<?php

namespace Waca\Pages\RequestAction;

use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\PdoDatabase;

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