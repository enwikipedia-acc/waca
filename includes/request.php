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

class accRequest
{

	/*
	* Check if the supplied host is a TOR node.
	*/
	public function checktor($addr)
	{
		// Creates empty array.
		$flags = array();
		
		// Sets tor variable to no.
		$flags['tor'] = "no";
		
		// Breaks the IP string up into an array.
		$p = explode(".", $addr);
		
		// Checks whether the user uses the IPv6 addy.
		// Returns the flags array with the false variable.
		if (strpos($addr, ':') != -1) {
			return $flags;
		}
		
		// Generates a new host name by means of the IP array and TOR string.
		$ahbladdr = $p['3'] . "." . $p['2'] . "." . $p['1'] . "." . $p['0'] . "." . "tor.ahbl.org";

		// Get the IP address corresponding to a given host name.
		$ahbl = gethostbyname($ahbladdr);
		
		// In the returned IP adress is one of the following, it is from the TOR network.
		// There is then a yes flag assigned to the flag array.
		if ($ahbl == "127.0.0.2") {
			$flags['transit'] = "yes";
			$flags['tor'] = "yes";
		}
		if ($ahbl == "127.0.0.3") {
			$flags['exit'] = "yes";
			$flags['tor'] = "yes";
		}
		
		// The flags array are returned to the isTor method.
		return ($flags);
	}

}
