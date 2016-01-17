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

class strings
{
	/**
	 * Formats a string to be used as a username.
	 * @deprecated StringFunctions class
	 * @param $username
	 * @return string
	 */
	public function struname($username)
	{
		return StringFunctions::formatAsUsername($username);
	}

	/**
	 * Formats a string to be used as an email (specifically strips whitespace
	 * from the beginning/end of the Email, as well as immediately before/after
	 * the @ in the Email).
	 * @deprecated StringFunctions class
	 * @param $email
	 * @return string
	 */
	public function stremail($email)
	{
		return StringFunctions::formatAsEmail($email);
	}
}
