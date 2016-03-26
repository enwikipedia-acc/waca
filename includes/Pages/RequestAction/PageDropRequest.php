<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

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