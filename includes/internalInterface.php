<?php
/**************************************************************
** English Wikipedia Account Request Interface               **
** Wikipedia Account Request Graphic Design by               **
** Charles Melbye is licensed under a Creative               **
** Commons Attribution-Noncommercial-Share Alike             **
** 3.0 United States License. All other code                 **
** released under Public Domain by the ACC                   **
** Development Team.                                         **
**             Developers:                                   **
** SQL ( http://en.wikipedia.org/User:SQL )                 **
** Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
** FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
** Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
** Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
** Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
** OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
** Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
** Chris G ( http://en.wikipedia.org/wiki/User:Chris_G )      **
**                                                           **
**************************************************************/

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client.

class internalInterface {
	public function showhowma() {
		/*
		* Show how many users are logged in, in the footer
		*/
		global $tsSQLlink;
		
		// Assigns the calculated amount of users to the new array.
		$howma = $this->gethowma();
		
		// Removes the howmany variable, as we only want the users online.
		unset ($howma['howmany']);
		
		// Get the user id for each of the users in the array.
		foreach ($howma as &$oluser) {
			// Sanitizes the username and get their record.
			$oluser = sanitize($oluser);
			$query = "SELECT * FROM acc_user WHERE user_name = '$oluser';";
			$result = mysql_query($query, $tsSQLlink);
			
			// Display an error message if the query didnt work.
			if (!$result) {
				sqlerror("Query failed: $query ERROR: " . mysql_error() . " f190","Database query error.");
			}
			
			// Uses the users row to gets its ID.
			$row = mysql_fetch_assoc($result);
			$uid = $row['user_id'];
			
			// Generates a link containing the username and the user ID.
			// Adds this link to the original array.
			$oluser = stripslashes($oluser);
			$oluser = "<a href=\"users.php?viewuser=$uid\">$oluser</a>";
		}
		
		// Destroys the oluser variable.
		// unset($oluser);
		
		// Adds all the items of the array into a string.
		// The string would contain links, as the link where added by the for loop.
		$out = "";
		$out = implode(", ", $howma);

		// Strips all the whitespaces from the string.
		$out = ltrim(rtrim($out));
		return ($out);
	}
	
	public function gethowma() {
		/*
		* Get how many people are logged in
		*/
		global $tsSQLlink;
		
		// Get the users active as of the last 5 mins, or 300 seconds.
		$last5min = time() - 300;
		
		// TODO: This produces a PHP Strict Standards error message.
		// Strict Standards: date() [function.date]: It is not safe to rely on the system's timezone settings.
		// Please use the date.timezone setting, the TZ environment variable or the date_default_timezone_set() function.
		// In case you used any of those methods and you are still getting this warning, you most likely misspelled the timezone identifier.
		$last5mins = date("Y-m-d H:i:s", $last5min); 		
		
		// Runs a query to get all the users that was active the last 5 minutes.
		$query = "SELECT user_name FROM acc_user WHERE user_lastactive > '$last5mins';";
		$result = mysql_query($query, $tsSQLlink);
		
		// Display an error message if the query didnt work.
		if (!$result) {
			sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
		}
		
		// Creates new array.
		$whoactive = array ();
		
		// Add each item from the SQL query into the array.
		while (list($user_name) = mysql_fetch_row($result)) {
			array_push($whoactive, $user_name);
		}
		
		// Counts the amount of elements in the array.
		$howma = count($whoactive);
		
		// Adds the amount of elements to the array.
		$whoactive['howmany'] = $howma;
		return ($whoactive);
	}
}
?>