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

class BootstrapSkin {
    public static function displayPublicHeader() {
        global $smarty;
    }
    
    public static function displayInternalHeader() {
        // userid
        // username
        // sitenotice
        global $smarty;
    }
    
    /**
     * Prints the public interface footer to the screen.
     */
    public static function displayPublicFooter() {
        global $smarty;
        
        $online = '';
        $smarty->assign("onlineusers", $online);
        
        $smarty->display("footer.tpl");
    }
    
	/**
     * Prints the internal interface footer to the screen.
     */
    public static function displayInternalFooter() {
        global $smarty, $internalInterface;
        
		$howma = $internalInterface->gethowma(true);
		$howout = $internalInterface->showhowma();
		if ($howma != 1) { // not equal to one, as zero uses the plural form too.
			$onlinemessage = "$howma Account Creators currently online (past 5 minutes): $howout";
        } else {
			$onlinemessage = "$howma Account Creator currently online (past 5 minutes): $howout";
        }
        
        $online = '<p class="span6 text-right"><small>' . $onlinemessage . '</small></p>';
        $smarty->assign("onlineusers", $online);
        
        $smarty->display("footer.tpl");
    }
    
    public static function displayAlertBox( $message, $type, $header ) {
        global $smarty;
    }
    
    public static function displayRequestForm( ) {
        global $smarty;
    }
}

class skin {
	
	/**
	 * Prints the public interface header to the screen.
	 */
	public function displayPheader() {
		// Displayes the interface header.
		global $messages;
		echo $messages->getMessage(8);
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
		
		// Checks whether the user must be forced to logout.
		$session->forceLogout($row['user_id']);
		
		// Gets the internal interface header.
		$out = $messages->getMessage('21');
		
		// Creates a blank varible for future use.
		$rethead = '';
		
		// Checks whether the user is logged in.
		if (isset ($_SESSION['user'])) {
			// Checks if the particular user has Admin rigths.
			if ($session->hasright($username, "Admin")) {
				// There are additional links added to the orginal header if so.
				$out = str_replace('%ADMINLINKSHERE%', "<a href=\"$tsurl/users.php\">User Management</a>", $out);				
			} else {
				$out = str_replace('%ADMINLINKSHERE%', '', $out);
			}
			
			// The header is assigned to this variable, no matter Admin or not.
			$rethead .= $out;
			
			// Generates the code for the header-info section. This includes a link to the user information and to log out.
			$rethead .= "<div id = \"header-info\">Logged in as <a href=\"$tsurl/statistics.php?page=Users&amp;user=" . $row['user_id'] . "\"><span title=\"View your user information\">" . $_SESSION['user'] . "</span></a>.  <a href=\"$tsurl/acc.php?action=logout\">Logout</a>?</div>\n";
			
			// Assigns the current date and time to a variable.
			// TODO: This produces a PHP Strict Standards error message. See next line.
			// Strict Standards: date() [function.date]: It is not safe to rely on the system's timezone settings.
			// Please use the date.timezone setting, the TZ environment variable or the date_default_timezone_set() function.
			// In case you used any of those methods and you are still getting this warning, you most likely misspelled the timezone identifier.
			$now = date("Y-m-d H-i-s"); 
			
			// Formulates and executes a SQL query to update the last time the user logged in, namely now.
			$query = "UPDATE acc_user SET user_lastactive = '$now' WHERE user_id = '" . $row['user_id'] . "';";
			$result = $tsSQL->query($query);
		
			// Display error upon failure.
			if (!$result) {
				$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"ERROR: database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
			}
		// This section is executed when the user is not logged in.
		} else {
			$out = str_replace('%ADMINLINKSHERE%', '', $out);
			
			// The header is assigned to this variable.
			$rethead .= $out;
			
			// Generates the code for the header-info section. This states that the user is not logged in, or the option to create an account.
			$rethead .= "<div id = \"header-info\">Not logged in.  <a href=\"$tsurl/acc.php\"><span title=\"Click here to return to the login form\">Log in</span></a>/<a href=\"$tsurl/acc.php?action=register\">Create account</a>?</div>\n";
		}
		// Prints the specific header-info section to the screen.
		echo $rethead;
	}
	
	/**
	 * Prints the public interface footer to the screen.
     * @deprecated
	 */
	public function displayPfooter() {
		// Displayes the interface header.
		BootstrapSkin::displayPublicFooter();

		// we probably want to output
		ob_end_flush();
	}
	
	/**
	 * Prints the internal interface footer to the screen.
     * @deprecated
	 */
	public function displayIfooter() {
        BootstrapSkin::displayInternalFooter();
		// we probably want to output
		ob_end_flush();
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
		global $messages;
		echo $messages->getMessage(6);
	}
}
?>
