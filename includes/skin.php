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
	public function displayheader() {
		global $tsSQL;
		$result = $tsSQL->query("SELECT * FROM acc_emails WHERE mail_id = '8';");
		if (!$result) {
			// TODO: Nice error message
			die("ERROR: No result returned.");
		}
		$row = mysql_fetch_assoc($result);
		echo $row['mail_text'];
	}
	
	public function displayfooter() {
		global $tsSQL;
		$result = $tsSQL->query("SELECT * FROM acc_emails WHERE mail_id = '22';");
		if (!$result) {
			// TODO: Nice error message
			die("ERROR: No result returned.");
		}
		$row = mysql_fetch_assoc($result);
		echo $row['mail_text'];
	}
}
?>