<?php

namespace Waca\Exceptions;

use Waca\DataObjects\User;
use Waca\PdoDatabase;

/**
 * Class AccessDeniedException
 *
 * Thrown when a logged-in user does not have permissions to access a page
 *
 * @package Waca\Exceptions
 */
class AccessDeniedException extends ReadableException
{
	public function getReadableError()
	{
		// TODO: set up something to display nicer error messages for new/declined/suspended users.

		header("HTTP/1.1 403 Forbidden");

		$this->setUpSmarty();

		// uck. We should still be able to access the database in this situation though.
		$database = PdoDatabase::getDatabaseConnection('acc');
		$currentUser = User::getCurrent($database);
		$this->assign('currentUser', $currentUser);
		$this->assign("loggedIn", (!$currentUser->isCommunityUser()));

		return $this->fetchTemplate("exception/access-denied.tpl");
	}
}