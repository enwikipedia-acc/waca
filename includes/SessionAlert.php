<?php
use Waca\WebRequest;

/**
 * Session Alerts
 *
 * Session alerts allow you to raise a message to be shown to the user on the
 * next page load, allowing you to still give a user a message after a
 * redirect. It's a lot nicer to deal with than redirecting to a message page,
 * or sending the user somewhere with error message parameters.
 *
 * It's advisable to use the static methods, unless you need something more
 * customised. The defaults should tie you over nicely.
 */
class SessionAlert
{
	private $message;
	private $title;
	private $type;
	private $closable;
	private $block;

	/**
	 * @param string $message
	 * @param string $title
	 * @param string $type
	 * @param bool   $closable
	 * @param bool   $block
	 */
	public function __construct($message, $title, $type = "alert-info", $closable = true, $block = true)
	{
		$this->message = $message;
		$this->title = $title;
		$this->type = $type;
		$this->closable = $closable;
		$this->block = $block;
	}

	/**
	 * Shows a quick one-liner message
	 *
	 * @param string $message
	 * @param string $type
	 */
	public static function quick($message, $type = "alert-info")
	{
		self::append(new SessionAlert($message, "", $type, true, false));
	}

	/**
	 * @param SessionAlert $alert
	 */
	public static function append(SessionAlert $alert)
	{
		$data = WebRequest::getSessionAlertData();
		$data[] = serialize($alert);
		WebRequest::setSessionAlertData($data);
	}

	/**
	 * Shows a quick one-liner success message
	 *
	 * @param string $message
	 */
	public static function success($message)
	{
		self::append(new SessionAlert($message, "", "alert-success", true, true));
	}

	/**
	 * Shows a quick one-liner warning message
	 *
	 * @param string $message
	 * @param string $title
	 */
	public static function warning($message, $title = "Warning!")
	{
		self::append(new SessionAlert($message, $title, "alert-warning", true, true));
	}

	/**
	 * Shows a quick one-liner error message
	 *
	 * @param string $message
	 * @param string $title
	 */
	public static function error($message, $title = "Error!")
	{
		self::append(new SessionAlert($message, $title, "alert-error", true, true));
	}

	/**
	 * Retrieves the alerts which have been saved to the session
	 * @return array
	 */
	public static function getAlerts()
	{
		$alertData = array();

		foreach (WebRequest::getSessionAlertData() as $a) {
			$alertData[] = unserialize($a);
		}

		return $alertData;
	}

	/**
	 * Clears the alerts from the session
	 */
	public static function clearAlerts()
	{
		WebRequest::clearSessionAlertData();
	}

	/**
	 * @return boolean
	 */
	public function isBlock()
	{
		return $this->block;
	}

	/**
	 * @return boolean
	 */
	public function isClosable()
	{
		return $this->closable;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}
}
