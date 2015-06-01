<?php

/**
 * Logger short summary.
 *
 * Logger description.
 *
 * @version 1.0
 * @author stwalkerster
 */
class Logger
{
	private static function createLogEntry(PdoDatabase $database, DataObject $object, $logaction, $comment = null, $user = null)
	{
		if ($user == null) {
			$user = User::getCurrent();    
		}
        
		$log = new Log();
		$log->setDatabase($database);
		$log->setAction($logaction);
		$log->setObjectId($object->getId());
		$log->setObjectType(get_class($object));
		$log->setUser($user);
		$log->setComment($comment);
		$log->save();
	}
    
	public static function emailConfirmed(PdoDatabase $database, Request $object)
	{
		self::createLogEntry($database, $object, "Email Confirmed", null, User::getCommunity());
	}

	#region Users
    
	public static function approvedUser(PdoDatabase $database, User $object)
	{
		self::createLogEntry($database, $object, "Approved");        
	}
    
	public static function declinedUser(PdoDatabase $database, User $object, $comment)
	{
		self::createLogEntry($database, $object, "Declined", $comment);        
	}
    
	public static function suspendedUser(PdoDatabase $database, User $object, $comment)
	{
		self::createLogEntry($database, $object, "Suspended", $comment);        
	}
   
	public static function demotedUser(PdoDatabase $database, User $object, $comment)
	{
		self::createLogEntry($database, $object, "Demoted", $comment);        
	}
    
	public static function promotedUser(PdoDatabase $database, User $object)
	{
		self::createLogEntry($database, $object, "Promoted");        
	}
    
	public static function renamedUser(PdoDatabase $database, User $object, $comment)
	{
		self::createLogEntry($database, $object, "Renamed", $comment);
	}
    
	public static function userPreferencesChange(PdoDatabase $database, User $object)
	{
		self::createLogEntry($database, $object, "Prefchange");
	}
    
	#endregion
    
	public static function interfaceMessageEdited(PdoDatabase $database, InterfaceMessage $object)
	{
		self::createLogEntry($database, $object, "Edited");
	}
    
	#region Welcome Templates
    
	public static function welcomeTemplateCreated(PdoDatabase $database, WelcomeTemplate $object)
	{
		self::createLogEntry($database, $object, "CreatedTemplate");
	}
    
	public static function welcomeTemplateEdited(PdoDatabase $database, WelcomeTemplate $object)
	{
		self::createLogEntry($database, $object, "EditedTemplate");
	}
    
	public static function welcomeTemplateDeleted(PdoDatabase $database, WelcomeTemplate $object)
	{
		self::createLogEntry($database, $object, "DeletedTemplate");
	}
    
	#endregion
    
	#region Bans
    
	public static function banned(PdoDatabase $database, Ban $object, $reason)
	{
		self::createLogEntry($database, $object, "Banned", $reason);
	}
    
	public static function unbanned(PdoDatabase $database, Ban $object, $reason)
	{
		self::createLogEntry($database, $object, "Unbanned", $reason);
	}
    
	#endregion
    
	#region Requests
    
	public static function deferRequest(PdoDatabase $database, Request $object, $target)
	{
		self::createLogEntry($database, $object, "Deferred to $target");
	}
    
	public static function closeRequest(PdoDatabase $database, Request $object, $target, $comment)
	{
		self::createLogEntry($database, $object, "Closed $target", $comment);
	}
    
	public static function reserve(PdoDatabase $database, Request $object)
	{
		self::createLogEntry($database, $object, "Reserved");
	}
    
	public static function breakReserve(PdoDatabase $database, Request $object)
	{
		self::createLogEntry($database, $object, "BreakReserve");
	}
    
	public static function unreserve(PdoDatabase $database, Request $object)
	{
		self::createLogEntry($database, $object, "Unreserved");
	}
    
	public static function editComment(PdoDatabase $database, Comment $object)
	{
		self::createLogEntry($database, $object->getRequestObject(), "EditComment-r");
		self::createLogEntry($database, $object, "EditComment-c");
	}

	public static function sendReservation(PdoDatabase $database, Request $object, User $target)
	{
		self::createLogEntry($database, $object, "SendReserved");
		self::createLogEntry($database, $object, "ReceiveReserved", null, $target);
	}

	#endregion
    
	#region Email templates
    
	public static function createEmail(PdoDatabase $database, EmailTemplate $object)
	{
		self::createLogEntry($database, $object, "CreatedEmail");
	}
    
	public static function editedEmail(PdoDatabase $database, EmailTemplate $object)
	{
		self::createLogEntry($database, $object, "EditedEmail");
	}
    
	#endregion

}
