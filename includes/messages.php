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

class messages {
	public function getMessage ($messageno) {
		global $tsSQL, $tsurl, $toolserver_database;
		$messageno = $tsSQL->escape($messageno);
		$query = "SELECT * FROM {$toolserver_database}.acc_emails WHERE mail_id = '$messageno';";
		$result = $tsSQL->query($query);
		if (!$result)
			$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"Database query error.");
		$row = mysql_fetch_assoc($result);
		$message = $row['mail_text'];
		$message = str_replace('%VERSION%', getToolVersion(), $message);
		$message = str_replace('%TSURL%', $tsurl, $message);
		return $message;
	}
	
	public function getMessageCount ($messageno) {
		global $tsSQL, $tsurl;
		$messageno = $tsSQL->escape($messageno);
		$query = "SELECT * FROM acc_emails WHERE mail_id = '$messageno';";
		$result = $tsSQL->query($query);
		if (!$result)
			$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"Database query error.");
		$row = mysql_fetch_assoc($result);
		$message = $row['mail_count'];
		return $message;
	}
	
	public function isEmail($messageNumber)
	{
		// override for drop
		if( $messageNumber == 0 ) return true;
		
		global $tsSQL;
		
		if (!preg_match('/^[0-9]*$/',$messageNumber)) {
			die('Invalid Input.');
		}
		
		$query = "SELECT mail_type FROM acc_emails WHERE mail_id = " . $tsSQL->escape($messageNumber) . ";";
		
		$result = $tsSQL->query($query);
		if(!$result)
		{
			$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"Database query error.");
			return false;
		} 
		$row = mysql_fetch_assoc($result);
		return ($row['mail_type'] == "Message");
	}

	public function getSitenotice()
	{
		$message = str_replace('%SITENOTICECOUNT%', $this->getMessageCount(31), $this->getMessage(20));
		$message = str_replace('%SITENOTICETEXT%', $this->getMessage(31), $message);
		return $message;
	}
}

?>
