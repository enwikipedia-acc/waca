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
		
		$howma = $this->gethowma();
		unset ($howma['howmany']);
		
		foreach ($howma as &$oluser) {
			$oluser = sanitize($oluser);
			$query = "SELECT * FROM acc_user WHERE user_name = '$oluser';";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				sqlerror("Query failed: $query ERROR: " . mysql_error() . " f190","Database query error.");
			$row = mysql_fetch_assoc($result);
			$uid = $row['user_id'];
				$oluser = stripslashes($oluser);
				$oluser = "<a href=\"users.php?viewuser=$uid\">$oluser</a>";
		}
		
		unset($oluser);
		$out = "";
		$out = implode(", ", $howma);
		$out = ltrim(rtrim($out));
		return ($out);
	}
	
	public function gethowma() {
		/*
		* Get how many people are logged in
		*/
		global $tsSQLlink;
		
		$last5min = time() - 300; // Get the users active as of the last 5 mins
		$last5mins = date("Y-m-d H:i:s", $last5min); // TODO: This produces a PHP Strict Standards error message. See next line
		//Strict Standards: date() [function.date]: It is not safe to rely on the system's timezone settings. Please use the date.timezone setting, the TZ environment variable or the date_default_timezone_set() function. In case you used any of those methods and you are still getting this warning, you most likely misspelled the timezone identifier.
		
		$query = "SELECT user_name FROM acc_user WHERE user_lastactive > '$last5mins';";
		$result = mysql_query($query, $tsSQLlink);
		
		if (!$result) {
			sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
		}
		
		$whoactive = array ();
		while (list($user_name) = mysql_fetch_row($result)) {
			array_push($whoactive, $user_name);
		}
		$howma = count($whoactive);
		$whoactive['howmany'] = $howma;
		return ($whoactive);
	}
}
?>