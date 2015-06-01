<?php

class ValidationError
{
	const NAME_EMPTY         = "name_empty";
	const NAME_EXISTS        = "name_exists";
	const NAME_EXISTS_SUL    = "name_exists";
	const NAME_NUMONLY       = "name_numonly";
	const NAME_INVALIDCHAR   = "name_invalidchar";
	const NAME_SANITISED     = "name_sanitised";
	const EMAIL_EMPTY        = "email_empty";
	const EMAIL_WIKIMEDIA    = "email_wikimedia";
	const EMAIL_INVALID      = "email_invalid";
	const EMAIL_MISMATCH     = "email_mismatch";
	const OPEN_REQUEST_NAME  = "open_request_name";
	const BANNED             = "banned";
	const BANNED_TOR         = "banned_tor";

	/**
	 * Summary of $errorCode
	 * @var string
	 */
	private $errorCode;

	/**
	 * Summary of $isError
	 * @var bool
	 */
	private $isError;

	/**
	 * Summary of __construct
	 * @param string $errorCode
	 * @param bool $isError
	 */
	public function __construct($errorCode, $isError = true)
	{
		$this->errorCode = $errorCode;
		$this->isError = $isError;
	}

	/**
	 * Summary of getErrorCode
	 * @return string
	 */
	public function getErrorCode()
	{
		return $this->errorCode;
	}

	/**
	 * Summary of isError
	 * @return bool
	 */
	public function isError()
	{
		return $this->isError;
	}
}
