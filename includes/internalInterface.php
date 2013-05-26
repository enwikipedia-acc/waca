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

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client.

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
	public function login($user, $ip, $password, $newaction) {
		global $tsSQL, $forceIdentification, $tsurl, $skin;
		$result = $tsSQL->query("SELECT * FROM acc_user WHERE user_name = \"$user\";");
		// Display error upon failure.
		if (!$result) 
			$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"ERROR: database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
		$row = mysql_fetch_assoc($result);
		// The password should always be the FIRST thing to be checked!
		if ( ! authutils::testCredentials( $password, $row['user_pass'] ) ) {
			$now = date("Y-m-d H-i-s");
			header("Location: $tsurl/acc.php?error=authfail");
			die();
		}
		if($row['user_identified'] == 0 && $forceIdentification == 1) {
			header("Location: $tsurl/acc.php?error=noid");
			die();
		}
		// Checks whether the user is new to ACC with a pending account.
		if ($row['user_level'] == "New") {
			// Display the header of the interface.
			$skin->displayPheader();
			echo "<h2>Account Pending</h2>";
			echo "I'm sorry, but, your account has not been approved by a site administrator yet. Please stand by.<br />\n";
			echo "</pre><br />";
			// Display the footer of the interface.
			echo "</div>";
			$skin->displayPfooter();
			die();
		}
		// Checks whether the user's account has been suspended.
		if ($row['user_level'] == "Suspended") {
			// Display the header of the interface.
			$skin->displayPheader();
			echo '<h2>Account Suspended</h2>';
			echo "I'm sorry, but, your account is presently suspended.<br />\n";
			// Checks whether there was a reason for the suspension.
			$reasonResult = $tsSQL->query('select log_cmt from acc_log where log_action = "Suspended" and log_pend = '.$row['user_id'].' order by log_time desc limit 1;');
			$reasonRow = mysql_fetch_assoc($reasonResult);
			echo "The reason given is shown below:<br /><pre>";
			echo '<b>' . $reasonRow['log_cmt'] . "</b></pre><br />";
			// Display the footer of the interface.
			echo "</div>";
			$skin->displayPfooter();
			die();
		}
		// If any of the above checks failed, the script should have terminated by now.
		if( ! authutils::isCredentialVersionLatest( $row['user_pass'] ) ) {
			$newCrypt = authutils::encryptPassword( $_POST['password'] );
			$tsSQL->query("UPDATE acc_user SET user_pass = '" . $newCrypt /* trusted */  . "' WHERE user_id = '" . $row['user_id'] /* trusted ID number */ . "' LIMIT 1;");
		}
		if ($row['user_forcelogout'] == 1)
			$tsSQL->query("UPDATE acc_user SET user_forcelogout = 0 WHERE user_name = \"" . $puser . "\"");
		// Assign values to certain Session variables.
		// The values are retrieved from the ACC database.
		$_SESSION['userID'] = $row['user_id'];
		$_SESSION['user'] = $row['user_name']; // While yes, the data from this has come DIRECTLY from the database, if it contains a " or a ', then it'll make the SQL query break, and that's a bad thing for MOST of the code.
		if ( isset( $_GET['newaction'] ) ) {
			$header = "Location: $tsurl/acc.php?action=".$_GET['newaction'];
			foreach ($_GET as $key => $get) {
				if ($key != "newaction" && $key != "nocheck" && $get != "login" )
					$header .= "&$key=$get";
			}
			header($header);
		}
		else {
				header("Location: $tsurl/acc.php");
		}
	}
	public function checkreqid($id) {
		global $skin, $tsSQL;
		/*
		 * Checks if a request exists and sanitizes it.
		*/
		
		// Make sure there are no invalid characters.
		if (!preg_match('/^[0-9]*$/',$id)) {
			// Notifies the user and stops the script.
			$skin->displayRequestMsg("The request ID supplied is invalid!");
			$skin->displayIfooter();
			die();
		}
		
		$sid = sanitise($id);
		
		// Formulates and executes SQL query to check if the request exists.
		$query = "SELECT Count(*) FROM acc_pend WHERE pend_id = '$sid';";
		$result = $tsSQL->query($query);
		if (!$result) 
			$tsSQL->showError(mysql_error(), "Database error");
		$row = mysql_fetch_row($result);
		
		// The query counted the amount of records with the particular request ID.
		// When the value is zero it is an indication that that request doesnt exist.
		if($row[0]==="0") {
			// Notifies the user and stops the script.
			$skin->displayRequestMsg("The request ID supplied is invalid!");
			$skin->displayIfooter();
			die();
		}
		return $sid;
	}
}
?>
