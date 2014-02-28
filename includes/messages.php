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
	/**
	 * Summary of getMessage
	 * @param mixed $messageno 
	 * @return mixed
     * @deprecated
	 */
	public function getMessage($messageno) 
    {
        return InterfaceMessage::getById($messageno, gGetDb())->getContentForDisplay();
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
}
