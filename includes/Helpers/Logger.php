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

	#region Display
	
	/**
	 * Summary of getRequestLogs
	 * @param int $requestId ID of the request to get logs for
	 * @param PdoDatabase $db Database to use
	 * @return array|bool
	 */
	public static function getRequestLogs($requestId, PdoDatabase $db)
	{
		$logStatement = $db->prepare(
			"SELECT * FROM log WHERE objecttype = 'Request' AND objectid = :requestid ORDER BY timestamp DESC");
		
		$result = $logStatement->execute(array( ":requestid" => $requestId));
		if ($result) {
			$data = $logStatement->fetchAll(PDO::FETCH_CLASS, "Log");
			foreach ($data as $entry) {
				$entry->isNew = false;
				$entry->setDatabase($db);
			}
			
			return $data;
		}
		
		return false;
	}
	
	/**
	 * Summary of getRequestLogsWithComments
	 * @param int $requestId 
	 * @param PdoDatabase $db 
	 * @return array
	 */
	public static function getRequestLogsWithComments($requestId, PdoDatabase $db)
	{
		$logs = self::getRequestLogs($requestId, $db);
		$comments = Comment::getForRequest($requestId, $db);
		
		$items = array_merge($logs, $comments);
		
		$sortKey = function(DataObject $item)
		{
			if ($item instanceof Log) {
				return $item->getTimestamp();
			}
			
			if ($item instanceof Comment) {
				return $item->getTime();
			}
			
			return 0;
		};
		
		do {
			$flag = false;
			
			$loopLimit = (count($items) - 1);
			for ($i = 0; $i < $loopLimit; $i++) {
				// are these two items out of order?
				if (strtotime($sortKey($items[$i])) > strtotime($sortKey($items[$i + 1]))) {
					// swap them
					$swap = $items[$i];
					$items[$i] = $items[$i + 1];
					$items[$i + 1] = $swap;
					
					// set a flag to say we've modified the array this time around
					$flag = true;
				}
			}
		}
		while ($flag);
		
		return $items;
	}
	
	/**
	 * Summary of getLogDescription
	 * @param Log $entry 
	 * @return string
	 */
	public static function getLogDescription(Log $entry)
	{
		$text = "Deferred to ";
		if (substr($entry->getAction(), 0, strlen($text)) == $text) {
			// Deferred to a different queue
			// This is exactly what we want to display.
			return $entry->getAction();	
		}
		
		$text = "Closed custom-n";
		if ($entry->getAction() == $text) {
			// Custom-closed
			return "closed (custom reason - account not created)";
		}
		
		$text = "Closed custom-y";
		if ($entry->getAction() == $text) {
			// Custom-closed
			return "closed (custom reason - account created)";
		}
		
		$text = "Closed 0";
		if ($entry->getAction() == $text) {
			// Dropped the request - short-circuit the lookup
			return "dropped request";
		}
		
		$text = "Closed ";
		if (substr($entry->getAction(), 0, strlen($text)) == $text) {
			// Closed with a reason - do a lookup here.
			$id = substr($entry->getAction(), strlen($text));
			$template = EmailTemplate::getById((int)$id, $entry->getDatabase());
			
			if ($template != false) {
				return "closed (" . $template->getName() . ")";
			}
			
		}
		
		// Fall back to the basic stuff
		$lookup = array(
			'Reserved' => 'reserved',
			'Email Confirmed' => 'email-confirmed',
			'Unreserved' => 'unreserved',
			'Approved' => 'approved',
			'Suspended' => 'suspended',
			'Banned' => 'banned',
			'Edited' => 'edited interface message',
			'Declined' => 'declined',
			'EditComment-c' => 'edited a comment',
			'EditComment-r' => 'edited a comment',
			'Unbanned' => 'unbanned',
			'Promoted' => 'promoted to tool admin',
			'BreakReserve' => 'forcibly broke the reservation',
			'Prefchange' => 'changed user preferences',
			'Renamed' => 'renamed',
			'Demoted' => 'demoted from tool admin',
			'ReceiveReserved' => 'received the reservation',
			'SendReserved' => 'sent the reservation',
			'EditedEmail' => 'edited email',
			'DeletedTemplate' => 'deleted template',
			'EditedTemplate' => 'edited template',
			'CreatedEmail' => 'created email',
			'CreatedTemplate' => 'created template',
			);
		
		if (array_key_exists($entry->getAction(), $lookup)) {
			return $lookup[$entry->getAction()];
		}
		
		// OK, I don't know what this is. Fall back to something sane.
		return "performed an unknown action";
	}
	
	#endregion
}
