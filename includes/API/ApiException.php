<?php

namespace Waca\API;

/**
 * ApiException
 */
class ApiException extends \Exception
{
	/**
	 * @param string $message
	 */
	public function __construct($message)
	{
		$this->message = $message;
	}
}
