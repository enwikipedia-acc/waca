<?php

namespace Waca\Exceptions;

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
		return $this->fetchTemplate("exception/access-denied.tpl");
	}
}