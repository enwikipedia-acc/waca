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

/**
 * AccBot class
 */
class AccBot
{
    private static $statement = false;
    
    /**
     * Send a message to IRC
     * @param string $message The message to send, with IRC colours included.
     */
    public static function send($message) 
    {
        if(self::$statement == false)
        {
            global $ircBotNotificationType;
            
            $db = gGetDb('notifications');
		    self::$statement = $db->prepare( "INSERT INTO notification (notif_type, notif_text) VALUES (:notiftype,:message);" );
		    self::$statement->bindValue(":notiftype", $ircBotNotificationType);
        }
        
		global $whichami;
                
		$blacklist = array("DCC", "CCTP", "PRIVMSG");
		$message = str_replace($blacklist, "(IRC Blacklist)", $message); //Lets stop DCC etc

		$msg = chr(2)."[$whichami]".chr(2).": $message";
        
		try 
        {
		    self::$statement->bindValue(":message", $msg);
		    self::$statement->execute();
        }
        catch(Exception $ex)
        {
            // blat any errors.
        }
        
		return;
	}
}
