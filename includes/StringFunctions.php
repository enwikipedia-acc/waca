<?php

namespace Waca;

class StringFunctions
{
	/**
	 * Formats a string to be used as a username.
	 *
	 * @param $username
	 *
	 * @return string
	 */
	public function formatAsUsername($username)
	{
		// trim whitespace from the ends
		$uname = mb_ereg_replace("^[ \t]+|[ \t]+$", "", $username);

		// convert first char to uppercase
		$uname = $this->ucfirst($uname);

		// replace spaces with underscores
		$uname = mb_ereg_replace("[ ]+", "_", $uname);

		// trim underscores from the end
		$uname = mb_ereg_replace("[_]+$", "", $uname);

		return $uname;
	}

	/**
	 * Formats a string to be used as an email (specifically strips whitespace
	 * from the beginning/end of the Email, as well as immediately before/after
	 * the @ in the Email).
	 *
	 * @param $email
	 *
	 * @return string
	 */
	public static function formatAsEmail($email)
	{
		// trim whitespace from the ends
		$newemail = mb_ereg_replace("^[ \t]+|[ \t]+$", "", $email);

		// trim whitespace from around the email address
		$newemail = mb_ereg_replace("[ \t]+@", "@", $newemail);
		$newemail = mb_ereg_replace("@[ \t]+", "@", $newemail);

		return $newemail;
	}

	/**
	 * Returns true if a string is a multibyte string
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public function isMultibyte($string)
	{
		return strlen($string) !== mb_strlen($string);
	}

	/**
	 * Make a string's first character uppercase
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function ucfirst($string)
	{
		if (ord($string) < 128) {
			return ucfirst($string);
		}
		else {
			return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
		}
	}
}
