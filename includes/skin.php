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
	 * Prints the interface header to the screen.
	 */
	public function displayheader() {
		// Displayes the interface header.
		$this->displayMessage(8);
	}
	
	/**
	 * Prints the interface footer to the screen.
	 */
	public function displayfooter() {
		// Displayes the interface header.
		$this->displayMessage(22);
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