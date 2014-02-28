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

class messages {
	public function getMessage($messageno) 
    {
        $messageObject = InterfaceMessage::getById($messageno, gGetDb());
        
		global $tsSQL;

		$message = $messageObject->getContent();
		
		if( strpos($message, "%VERSION%") !== false ) {
			$message = str_replace('%VERSION%', getToolVersion(), $message);
		}
		
		$message = str_replace('%TSURL%', $tsurl, $message);
		return $message;
	}
	
	public function getMessageCount ($messageno) 
    {
        $message = InterfaceMessage::getById($messageno, gGetDb());

		return $message->getUpdateCounter();
	}
	
	public function isEmail($messageNumber)
	{
		// override for drop
		if( $messageNumber == 0 ) return true;
		
		if (!preg_match('/^[0-9]*$/',$messageNumber)) {
			die('Invalid Input.');
		}
		
		$id = EmailTemplate::getById($messageNumber, gGetDb());
		
		if ($id)
			return true;
		else
			return false;
	}

	public function getSitenotice()
	{
		$message = str_replace('%SITENOTICECOUNT%', $this->getMessageCount(31), $this->getMessage(20));
		$message = str_replace('%SITENOTICETEXT%', $this->getMessage(31), $message);
		return $this->getMessage(31);
	}
}

?>
