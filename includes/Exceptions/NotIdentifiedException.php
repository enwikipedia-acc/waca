<?php

namespace Waca\Exceptions;

use PdoDatabase;
use User;

class NotIdentifiedException extends ReadableException
{
	/**
	 * Returns a readable HTML error message that's displayable to the user using templates.
	 * @return string
	 */
	public function getReadableError()
	{
		header("HTTP/1.1 403 Forbidden");

		$this->setUpSmarty();

		// uck. We should still be able to access the database in this situation though.
		$database = PdoDatabase::getDatabaseConnection('acc');
		$currentUser = User::getCurrent($database);
		$this->assign('currentUser', $currentUser);
		$this->assign("loggedIn", (!$currentUser->isCommunityUser()));


		return $this->fetchTemplate("exception/not-identified.tpl");
	}
}