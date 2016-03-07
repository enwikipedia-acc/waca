<?php

namespace Waca\Validation;

use Exception;

class ValidationError
{
	const NAME_EMPTY = "name_empty";
	const NAME_EXISTS = "name_exists";
	const NAME_EXISTS_SUL = "name_exists";
	const NAME_NUMONLY = "name_numonly";
	const NAME_INVALIDCHAR = "name_invalidchar";
	const NAME_SANITISED = "name_sanitised";
	const EMAIL_EMPTY = "email_empty";
	const EMAIL_WIKIMEDIA = "email_wikimedia";
	const EMAIL_INVALID = "email_invalid";
	const EMAIL_MISMATCH = "email_mismatch";
	const OPEN_REQUEST_NAME = "open_request_name";
	const BANNED = "banned";
	const BANNED_TOR = "banned_tor";
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
	 *
	 * @param string $errorCode
	 * @param bool   $isError
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
	 * @return string
	 * @throws Exception
	 */
	public function getErrorMessage()
	{
		switch ($this->errorCode) {
			case self::NAME_EMPTY:
				return 'You\'ve not chosen a username!';
			case self::NAME_EXISTS:
			case self::NAME_EXISTS_SUL:
				return 'I\'m sorry, but the username you selected is already taken. Please try another. Please note that Wikipedia automatically capitalizes the first letter of any user name, therefore [[User:example]] would become [[User:Example]].';
			case self::NAME_NUMONLY:
				return 'The username you chose is invalid: it consists entirely of numbers. Please retry with a valid username.';
			case self::NAME_INVALIDCHAR:
				return 'There appears to be an invalid character in your username. Please note that the following characters are not allowed: <code># @ / &lt; &gt; [ ] | { }</code>';
			case self::NAME_SANITISED:
				return 'Your requested username has been automatically adjusted due to technical restrictions. Underscores have been replaced with spaces, and the first character has been capitalised.';
			case self::EMAIL_EMPTY:
				return 'You need to supply an email address.';
			case self::EMAIL_WIKIMEDIA:
				return 'Please provide your email address here.';
			case self::EMAIL_INVALID:
				return 'Invalid E-mail address supplied. Please check you entered it correctly.';
			case self::EMAIL_MISMATCH:
				return 'The email addresses you entered do not match. Please try again.';
			case self::OPEN_REQUEST_NAME:
				return 'There is already an open request with this name in this system.';
			case self::BANNED:
				return 'I\'m sorry, but you are currently banned from requesting accounts using this tool. However, you can still send an email to accounts-enwiki-l@lists.wikimedia.org to request an account.';
			case self::BANNED_TOR:
				return 'Tor exit nodes are currently banned from using this tool due to excessive abuse. Please note that Tor is also currently banned from editing Wikipedia.';
		}

		throw new Exception('Unknown validation error');
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
