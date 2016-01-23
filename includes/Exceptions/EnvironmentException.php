<?php

namespace Waca\Exceptions;

use Exception;

/**
 * Class EnvironmentException
 *
 * To be used when the tool environment does not support running the tool.
 *
 * The main use of this is likely to be when the database fails.
 *
 * @package Waca\Exceptions
 */
class EnvironmentException extends Exception
{
	/**
	 * EnvironmentException constructor.
	 * @param string $friendlyMessage
	 */
	public function __construct($friendlyMessage)
	{
		parent::__construct($friendlyMessage);
	}
}