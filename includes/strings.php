<?php
/**************************************************************************
 **********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/
if (!defined("ACC")) {
	die();
} // Invalid entry point

class strings
{
	/**
	 * Multibyte version of ucfirst() since no such function is included in PHP by default.
	 * @param string $string
	 * @deprecated StringFunctions class
	 */
	private function mb_ucfirst($string)
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
	 * @deprecated StringFunctions class
	 */
	public function struname($username)
	{
		$uname = mb_ereg_replace("^[ \t]+|[ \t]+$", "", $username);
		$uname = $this->mb_ucfirst($uname);
		$uname = mb_ereg_replace("[ ]+", "_", $uname);
		$uname = mb_ereg_replace("[_]+$", "", $uname);
		return $uname;
	}
	
	/**
	 * Formats a string to be used as an email (specifically strips whitespace
	 * from the beginning/end of the Email, as well as immediately before/after
	 * the @ in the Email).
	 * @deprecated StringFunctions class
	 */
	public function stremail($email)
	{
		$newemail = mb_ereg_replace("^[ \t]+|[ \t]+$", "", $email);
		$newemail = mb_ereg_replace("[ \t]+@", "@", $newemail);
		$newemail = mb_ereg_replace("@[ \t]+", "@", $newemail);
		return $newemail;
	}
}
