<?php

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
	 * @param bool $closable
	 * @param bool $block
	 */
	public function __construct($message, $title, $type = "alert-info", $closable = true, $block = true)
	{
		$this->message = $message;
		$this->title = $title;
		$this->type = $type;
		$this->closable = $closable;
		$this->block = $block;
	}

	public function getAlertBox()
	{
		return BootstrapSkin::displayAlertBox($this->message, $this->type, $this->title, $this->block, $this->closable, true);
	}

	/**
	 * Shows a quick one-liner message
	 * @param string $message
	 * @param string $type
	 */
	public static function quick($message, $type = "alert-info")
	{
		self::append(new SessionAlert($message, "", $type, true, false));
	}

	public static function success($message)
	{
		self::append(new SessionAlert($message, "", "alert-success", true, true));
	}

	public static function warning($message, $title = "Warning!")
	{
		self::append(new SessionAlert($message, $title, "alert-warning", true, true));
	}

	public static function error($message, $title = "Error!")
	{
		self::append(new SessionAlert($message, $title, "alert-error", true, true));
	}

	public static function append(SessionAlert $alert)
	{
		$data = array();
		if (isset($_SESSION['alerts'])) {
			$data = $_SESSION['alerts'];
		}

		$data[] = serialize($alert);

		$_SESSION['alerts'] = $data;
	}

	public static function retrieve()
	{
		$block = array();
		if (isset($_SESSION['alerts'])) {
			foreach ($_SESSION['alerts'] as $a) {
				$block[] = unserialize($a);
			}
		}

		$_SESSION['alerts'] = array();

		return $block;
	}
}
