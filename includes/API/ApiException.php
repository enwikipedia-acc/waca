<?php

namespace Waca\API;

use Exception;

/**
 * ApiException
 */
class ApiException extends Exception
{
	/**
	 * @param string $message
	 */
	public function __construct($message)
	{
		$this->message = $message;
	}
}
