<?php

namespace Waca\Exceptions;

use Exception;

class ApplicationLogicException extends ReadableException
{
	/**
	 * Returns a readable HTML error message that's displayable to the user using templates.
	 * @return string
	 */
	public function getReadableError()
	{
		$this->setUpSmarty();

		$this->assign('message', $this->getMessage());
		return $this->fetchTemplate("exception/application-logic.tpl");
	}
}