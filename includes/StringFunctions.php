<?php

class StringFunctions
{
	/**
	 * @param string $string
	 * @return string
	 */
	private static function mb_ucfirst($string)
	{
		$strlen = mb_strlen($string);
		$substr = mb_substr($string, 0, 1);
		$substr2 = mb_substr($string, 1, $strlen - 1);
		$upstring = mb_strtoupper($substr);
		$ustring = $upstring . $substr2;
		return $ustring;
	}

	/**
	 * Formats a string to be used as a username.
	 * @param $username
	 * @return string
	 */
	public static function formatAsUsername($username)
	{
		$uname = mb_ereg_replace("^[ \t]+|[ \t]+$", "", $username);
		$uname = self::mb_ucfirst($uname);
		$uname = mb_ereg_replace("[ ]+", "_", $uname);
		$uname = mb_ereg_replace("[_]+$", "", $uname);
		return $uname;
	}

	/**
	 * Formats a string to be used as an email (specifically strips whitespace
	 * from the beginning/end of the Email, as well as immediately before/after
	 * the @ in the Email).
	 * @param $email
	 * @return string
	 */
	public static function formatAsEmail($email)
	{
		$newemail = mb_ereg_replace("^[ \t]+|[ \t]+$", "", $email);
		$newemail = mb_ereg_replace("[ \t]+@", "@", $newemail);
		$newemail = mb_ereg_replace("@[ \t]+", "@", $newemail);
		return $newemail;
	}

}
