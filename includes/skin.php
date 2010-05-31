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

class skin {
	
	/**
	 * Prints a specific interface message to the screen.
	 * @param $msgID The ID of the message to print to the screen.
	 * @deprecated Use messages->getMessage() instead. 2010-03-06 stw
	 */
	public function displayMessage($msgID) {
		global $messages;
		echo $messages->getMessage($msgID);
	}
	
	/**
	 * Prints the public interface header to the screen.
	 */
	public function displayPheader() {
		// Displayes the interface header.
		$this->displayMessage(8);
	}
	
	/**
	 * Prints the internal interface header to the screen.
	 * @param $username The username of the curretly logged in user.
	 */
	public function displayIheader($username) {
		// Gets the needed objects.
		global $tsSQL, $messages, $session, $tsurl;
		
		// Escapes the username for MySQL.
		$suin = $tsSQL->escape($username);
		
		// Formulates and executes the SQL query to get details regarding the user.
		$query = "SELECT * FROM acc_user WHERE user_name = '$suin' LIMIT 1;";
		$result = $tsSQL->query($query);
		
		// Display error upon failure.
		if (!$result) {
			$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"ERROR: database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
		}
		
		// Fetch the result row as an array.
		$row = mysql_fetch_assoc($result);
		
		// Sets the user_id according to the returned data.
		$_SESSION['user_id'] = $row['user_id'];
		
		// Checks whether the user must be forced to logout.
		$session->forceLogout($_SESSION['user_id']);
		
		// Gets the internal interface header.
		$out = $messages->getMessage('21');
		
		// Creates a blank varible for future use.
		$rethead = '';
		
		// Checks whether the user is logged in.
		if (isset ($_SESSION['user'])) {
			// Checks if the particular user has Admin rigths.
			if ($session->hasright($username, "Admin")) {
				// There are additional links added to the orginal header if so.
				$out = preg_replace('/\<a href\=\"$tsurl/acc\.php\?action\=messagemgmt\"\>Message Management\<\/a\>/', "\n<a href=\"$tsurl/acc.php?action=messagemgmt\">Message Management</a>\n<a href=\"$tsurl/users.php\">User Management</a>\n", $out);				
			}
			
			// The header is assigned to this variable, no matter Admin or not.
			$rethead .= $out;
			
			// Generates the code for the header-info section. This includes a link to the user information and to log out.
			$rethead .= "<div id = \"header-info\">Logged in as <a href=\"statistics.php?page=Users&amp;user=" . $_SESSION['user_id'] . "\"><span title=\"View your user information\">" . $_SESSION['user'] . "</span></a>.  <a href=\"acc.php?action=logout\">Logout</a>?</div>\n";
			
			// Assigns the current date and time to a variable.
			// TODO: This produces a PHP Strict Standards error message. See next line.
			// Strict Standards: date() [function.date]: It is not safe to rely on the system's timezone settings.
			// Please use the date.timezone setting, the TZ environment variable or the date_default_timezone_set() function.
			// In case you used any of those methods and you are still getting this warning, you most likely misspelled the timezone identifier.
			$now = date("Y-m-d H-i-s"); 
			
			// Formulates and executes a SQL query to update the last time the user logged in, namely now.
			$query = "UPDATE acc_user SET user_lastactive = '$now' WHERE user_id = '" . $_SESSION['user_id'] . "';";
			$result = $tsSQL->query($query);
		
			// Display error upon failure.
			if (!$result) {
				$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"ERROR: database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
			}
		// This section is executed when the user is not logged in.
		} else {
			// The header is assigned to this variable.
			$rethead .= $out;
			
			// Generates the code for the header-info section. This states that the user is not logged in, or the option to create an account.
			$rethead .= "<div id = \"header-info\">Not logged in.  <a href=\"acc.php\"><span title=\"Click here to return to the login form\">Log in</span></a>/<a href=\"acc.php?action=register\">Create account</a>?</div>\n";
		}
		// Prints the specific header-info section to the screen.
		echo $rethead;
	}
	
	/**
	 * Prints the public interface footer to the screen.
	 */
	public function displayPfooter() {
		// Displayes the interface header.
		$this->displayMessage(23);
	}
	
	/**
	 * Prints the internal interface footer to the screen.
	 */
	public function displayIfooter() {
		global $enableLastLogin, $messages, $internalInterface;
		if ($enableLastLogin) {
			$timestamp = "at ".date('H:i',$_SESSION['lastlogin_time']);
			if (date('jS \of F Y',$_SESSION['lastlogin_time'])==date('jS \of F Y')) {
				$timestamp .= " today";
			} else {
				$timestamp .= " on the ".date('jS \of F, Y',$_SESSION['lastlogin_time']);
			}
			if ($_SESSION['lastlogin_ip']==$_SERVER['REMOTE_ADDR']) {
				$out2 = "<div align=\"center\"><small>You last logged in from this computer $timestamp.</small></div>";
			} else {
				$out2 = "<div align=\"center\"><small>You last logged in from <a href=\"http://toolserver.org/~overlordq/cgi-bin/whois.cgi?lookup=".$_SESSION['lastlogin_ip']."\">".$_SESSION['lastlogin_ip']."</a> $timestamp.</small></div>";
			}
		} else {
			$out2 = '';
		}
		
		$howmany = array ();
		$howmany = $internalInterface->gethowma(true);
		$howout = $internalInterface->showhowma();
		$howma = $howmany['howmany'];
		echo "</div>"; //Add this right before the footer message since a div close tag inside the message itself will cause HTML validation errors in the public interface. 
		$out = $messages->getMessage('23');
		if ($howma != 1) // not equal to one, as zero uses the plural form too.
			$out = preg_replace('/\<br \/\>\<br \/\>/', "<br /><div align=\"center\"><small>$howma Account Creators currently online (past 5 minutes): $howout</small></div>\n$out2", $out);
		else
			$out = preg_replace('/\<br \/\>\<br \/\>/', "<br /><div align=\"center\"><small>$howma Account Creator currently online (past 5 minutes): $howout</small></div>\n$out2", $out);
		echo $out;
	}
	
	/**
	 * Prints a request message to the screen.
	 * @param $message The message to print to the screen.
	 */
	public function displayRequestMsg($message) {
		// Prints a request message to the screen using the message variable.
		echo "<div class=\"request-message\">" . $message . "</div>";
	}
	
	/**
	 * Prints the account request form to the screen.
	 */
	public function displayRequest() {
		// Displayes the account request form.
		$this->displayMessage(6);
	}
}
?>