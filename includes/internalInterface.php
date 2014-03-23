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

class internalInterface {
	public function showhowma() {
		/*
		* Show how many users are logged in, in the footer
		*/
		
		$users = $this->gethowma();
		$out = array();
		
		foreach ($users as $user) {
			array_push($out,"<a href=\"statistics.php?page=Users&amp;user=".$user['user_id']."\">".$user['user_name']."</a>");
		}
		
		$out = implode(", ", $out);

		// Strips all the whitespaces from the string.
		$out = trim($out);
		return $out;
	}
	
	public function gethowma($count=false) {
		/*
		* Return the users that are currently logged in.
		*/
		global $tsSQL;
		
		// Get the users active as of the last 5 mins, or 300 seconds.
		$last5min = time() - 300;
		
		// TODO: This produces a PHP Strict Standards error message.
		// Strict Standards: date() [function.date]: It is not safe to rely on the system's timezone settings.
		// Please use the date.timezone setting, the TZ environment variable or the date_default_timezone_set() function.
		// In case you used any of those methods and you are still getting this warning, you most likely misspelled the timezone identifier.
		$last5mins = date("Y-m-d H:i:s", $last5min); 		
		
		// Runs a query to get all the users that was active the last 5 minutes.
		if ($count) {
			$query = "SELECT count(user_id) as num FROM acc_user WHERE user_lastactive > '$last5mins';";
		} else {
			$query = "SELECT user_name,user_id FROM acc_user WHERE user_lastactive > '$last5mins';";
		}
		$result = $tsSQL->query($query);
		
		// Display an error message if the query didnt work.
		if (!$result) {
			sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
		}
		
		if ($count) {
			$row = mysql_fetch_assoc($result);
			return $row['num'];
		}
		
		// Creates new array.
		$whoactive = array ();
		
		// Add each item from the SQL query into the array.
		while ($row = mysql_fetch_assoc($result)) {
			array_push($whoactive, $row);
		}
		
		return $whoactive;
	}
}
?>
