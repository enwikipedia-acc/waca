<?php

namespace Waca\Exceptions;

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
		return $this->fetchTemplate("exception/not-identified.tpl");
	}
}