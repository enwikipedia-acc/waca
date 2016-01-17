<?php

/**
 * Transaction Exception
 *
 * Raise this inside a transactionally() block and you'll terminate the
 * transaction and show an error message to the user.
 */
class TransactionException extends Exception
{
	private $title;
	private $alertType;

	/**
	 * @param string $message
	 * @param string $title
	 * @param string $alertType
	 * @param int $code
	 * @param null|Exception $previous
	 */
	public function __construct($message, $title = "Error occured during transaction", $alertType = "alert-error", $code = 0, Exception $previous = null)
	{
		$this->title = $title;
		$this->alertType = $alertType;
		parent::__construct($message, $code, $previous);
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getAlertType()
	{
		return $this->alertType;
	}
}
