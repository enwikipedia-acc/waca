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

// accbot class
class accbotSend {
	public function send($message) {
		global $whichami, $ircBotNotificationType;
		$message = html_entity_decode($message,ENT_COMPAT,'UTF-8'); // If a message going to the bot was for whatever reason sent through sanitze() earlier, reverse it. 
		$message = stripslashes($message);
		$blacklist = array("DCC", "CCTP", "PRIVMSG");
		$message = str_replace($blacklist, "(IRC Blacklist)", $message); //Lets stop DCC etc

		$msg = chr(2)."[$whichami]".chr(2).": $message";
		
		$db = gGetDb('notifications');
		
		$q = $db->prepare( "INSERT INTO notification values (null,null,1,:message);" );
		$q->bindParam(":message", $msg);
		$q->execute();
		
		return;
	}
}

?>
