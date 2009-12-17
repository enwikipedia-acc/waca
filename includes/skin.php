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
	 */
	public function displayMessage($msgID) {
		// Get DB object from index file.
		global $tsSQL;
		
		// Formulates and executes SQL query to return the required message.
		$result = $tsSQL->query("SELECT * FROM acc_emails WHERE mail_id = '$msgID';");
		
		// Display an error message if the query fails.
		if (!$result) {
			// TODO: Nice error message
			die("ERROR: No result returned.");
		}
		
		// Assigns the required row to a variable and print it to the screen.
		$row = mysql_fetch_assoc($result);
		echo $row['mail_text'];
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
	 */
	public function displayIheader() {
		// Displayes the interface header.
		$this->displayMessage(21);
	}
	
	/**
	 * Prints the public interface footer to the screen.
	 */
	public function displayPfooter() {
		// Displayes the interface header.
		$this->displayMessage(22);
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
	$out = $messages->getMessage('23');
	if ($howma != 1) // not equal to one, as zero uses the plural form too.
		$out = preg_replace('/\<br \/\>\<br \/\>/', "<br /><div align=\"center\"><small>$howma Account Creators currently online (past 5 minutes): $howout</small></div>\n$out2", $out);
	else
		$out = preg_replace('/\<br \/\>\<br \/\>/', "<br /><div align=\"center\"><small>$howma Account Creator currently online (past 5 minutes): $howout</small></div>\n$out2", $out);
	return $out;
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