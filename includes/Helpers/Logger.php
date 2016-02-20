<?php

/**
 * Helper class for creating log entries
 *
 * Logger description.
 *
 * @version 1.0
 * @author  stwalkerster
 */
class Logger
{
	/**
	 * @param PdoDatabase $database
	 * @param Request     $object
	 */
	public static function emailConfirmed(PdoDatabase $database, Request $object)
	{
		self::createLogEntry($database, $object, "Email Confirmed", null, User::getCommunity());
	}

	/**
	 * @param PdoDatabase $database
	 * @param DataObject  $object
	 * @param string      $logAction
	 * @param null|string $comment
	 * @param User        $user
	 *
	 * @throws Exception
	 */
	private static function createLogEntry(
		PdoDatabase $database,
		DataObject $object,
		$logAction,
		$comment = null,
		$user = null
	) {
		if ($user == null) {
			$user = User::getCurrent();
		}

		$log = new Log();
		$log->setDatabase($database);
		$log->setAction($logAction);
		$log->setObjectId($object->getId());
		$log->setObjectType(get_class($object));
		$log->setUser($user);
		$log->setComment($comment);
		$log->save();
	}

	#region Users

	/**
	 * @param PdoDatabase $database
	 * @param User $user
	 */
	public static function newUser(PdoDatabase $database, User $user)
	{
		self::createLogEntry($database, $user, 'Registered', null, User::getCommunity());
	}

	/**
	 * @param PdoDatabase $database
	 * @param User        $object
	 */
	public static function approvedUser(PdoDatabase $database, User $object)
	{
		self::createLogEntry($database, $object, "Approved");
	}

	/**
	 * @param PdoDatabase $database
	 * @param User        $object
	 * @param string      $comment
	 */
	public static function declinedUser(PdoDatabase $database, User $object, $comment)
	{
		self::createLogEntry($database, $object, "Declined", $comment);
	}

	/**
	 * @param PdoDatabase $database
	 * @param User        $object
	 * @param string      $comment
	 */
	public static function suspendedUser(PdoDatabase $database, User $object, $comment)
	{
		self::createLogEntry($database, $object, "Suspended", $comment);
	}

	/**
	 * @param PdoDatabase $database
	 * @param User        $object
	 * @param string      $comment
	 */
	public static function demotedUser(PdoDatabase $database, User $object, $comment)
	{
		self::createLogEntry($database, $object, "Demoted", $comment);
	}

	/**
	 * @param PdoDatabase $database
	 * @param User        $object
	 */
	public static function promotedUser(PdoDatabase $database, User $object)
	{
		self::createLogEntry($database, $object, "Promoted");
	}

	/**
	 * @param PdoDatabase $database
	 * @param User        $object
	 * @param string      $comment
	 */
	public static function renamedUser(PdoDatabase $database, User $object, $comment)
	{
		self::createLogEntry($database, $object, "Renamed", $comment);
	}

	/**
	 * @param PdoDatabase $database
	 * @param User        $object
	 */
	public static function userPreferencesChange(PdoDatabase $database, User $object)
	{
		self::createLogEntry($database, $object, "Prefchange");
	}

	#endregion

	/**
	 * @param PdoDatabase      $database
	 * @param InterfaceMessage $object
	 */
	public static function interfaceMessageEdited(PdoDatabase $database, InterfaceMessage $object)
	{
		self::createLogEntry($database, $object, "Edited");
	}

	#region Welcome Templates

	/**
	 * @param PdoDatabase     $database
	 * @param WelcomeTemplate $object
	 */
	public static function welcomeTemplateCreated(PdoDatabase $database, WelcomeTemplate $object)
	{
		self::createLogEntry($database, $object, "CreatedTemplate");
	}

	/**
	 * @param PdoDatabase     $database
	 * @param WelcomeTemplate $object
	 */
	public static function welcomeTemplateEdited(PdoDatabase $database, WelcomeTemplate $object)
	{
		self::createLogEntry($database, $object, "EditedTemplate");
	}

	/**
	 * @param PdoDatabase     $database
	 * @param WelcomeTemplate $object
	 */
	public static function welcomeTemplateDeleted(PdoDatabase $database, WelcomeTemplate $object)
	{
		self::createLogEntry($database, $object, "DeletedTemplate");
	}

	#endregion

	#region Bans

	/**
	 * @param PdoDatabase $database
	 * @param Ban         $object
	 * @param string      $reason
	 */
	public static function banned(PdoDatabase $database, Ban $object, $reason)
	{
		self::createLogEntry($database, $object, "Banned", $reason);
	}

	/**
	 * @param PdoDatabase $database
	 * @param Ban         $object
	 * @param string      $reason
	 */
	public static function unbanned(PdoDatabase $database, Ban $object, $reason)
	{
		self::createLogEntry($database, $object, "Unbanned", $reason);
	}

	#endregion

	#region Requests

	/**
	 * @param PdoDatabase $database
	 * @param Request     $object
	 * @param string      $target
	 */
	public static function deferRequest(PdoDatabase $database, Request $object, $target)
	{
		self::createLogEntry($database, $object, "Deferred to $target");
	}

	/**
	 * @param PdoDatabase $database
	 * @param Request     $object
	 * @param integer     $target
	 * @param string      $comment
	 */
	public static function closeRequest(PdoDatabase $database, Request $object, $target, $comment)
	{
		self::createLogEntry($database, $object, "Closed $target", $comment);
	}

	/**
	 * @param PdoDatabase $database
	 * @param Request     $object
	 */
	public static function reserve(PdoDatabase $database, Request $object)
	{
		self::createLogEntry($database, $object, "Reserved");
	}

	/**
	 * @param PdoDatabase $database
	 * @param Request     $object
	 */
	public static function breakReserve(PdoDatabase $database, Request $object)
	{
		self::createLogEntry($database, $object, "BreakReserve");
	}

	/**
	 * @param PdoDatabase $database
	 * @param Request     $object
	 */
	public static function unreserve(PdoDatabase $database, Request $object)
	{
		self::createLogEntry($database, $object, "Unreserved");
	}

	/**
	 * @param PdoDatabase $database
	 * @param Comment     $object
	 * @param Request     $request
	 */
	public static function editComment(PdoDatabase $database, Comment $object, Request $request)
	{
		self::createLogEntry($database, $request, "EditComment-r");
		self::createLogEntry($database, $object, "EditComment-c");
	}

	/**
	 * @param PdoDatabase $database
	 * @param Request     $object
	 * @param User        $target
	 */
	public static function sendReservation(PdoDatabase $database, Request $object, User $target)
	{
		self::createLogEntry($database, $object, "SendReserved");
		self::createLogEntry($database, $object, "ReceiveReserved", null, $target);
	}

	/**
	 * @param PdoDatabase $database
	 * @param Request     $object
	 * @param string      $comment
	 */
	public static function sentMail(PdoDatabase $database, Request $object, $comment)
	{
		self::createLogEntry($database, $object, "SentMail", $comment);
	}
	#endregion

	#region Email templates

	/**
	 * @param PdoDatabase   $database
	 * @param EmailTemplate $object
	 */
	public static function createEmail(PdoDatabase $database, EmailTemplate $object)
	{
		self::createLogEntry($database, $object, "CreatedEmail");
	}

	/**
	 * @param PdoDatabase   $database
	 * @param EmailTemplate $object
	 */
	public static function editedEmail(PdoDatabase $database, EmailTemplate $object)
	{
		self::createLogEntry($database, $object, "EditedEmail");
	}

	#endregion

	#region Display

	/**
	 * Summary of getRequestLogsWithComments
	 *
	 * @param int         $requestId
	 * @param PdoDatabase $db
	 *
	 * @return array
	 */
	public static function getRequestLogsWithComments($requestId, PdoDatabase $db)
	{
		$logs = self::getRequestLogs($requestId, $db);
		$comments = Comment::getForRequest($requestId, $db);

		$items = array_merge($logs, $comments);

		$sortKey = function(DataObject $item) {
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
	 * Summary of getRequestLogs
	 *
	 * @param int         $requestId ID of the request to get logs for
	 * @param PdoDatabase $db        Database to use
	 *
	 * @return array|bool
	 */
	public static function getRequestLogs($requestId, PdoDatabase $db)
	{
		$logStatement = $db->prepare(
			<<<SQL
SELECT * FROM log
WHERE objecttype = 'Request' AND objectid = :requestId
ORDER BY timestamp DESC
SQL
		);

		$result = $logStatement->execute(array(":requestId" => $requestId));
		if ($result) {
			$data = $logStatement->fetchAll(PDO::FETCH_CLASS, "Log");

			/** @var Log $entry */
			foreach ($data as $entry) {
				$entry->isNew = false;
				$entry->setDatabase($db);
			}

			return $data;
		}

		return false;
	}

	/**
	 * Summary of getLogDescription
	 *
	 * @param Log $entry
	 *
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
			/** @var EmailTemplate $template */
			$template = EmailTemplate::getById((int)$id, $entry->getDatabase());

			if ($template != false) {
				return "closed (" . $template->getName() . ")";
			}
		}

		// Fall back to the basic stuff
		$lookup = array(
			'Reserved'        => 'reserved',
			'Email Confirmed' => 'email-confirmed',
			'Unreserved'      => 'unreserved',
			'Approved'        => 'approved',
			'Suspended'       => 'suspended',
			'Banned'          => 'banned',
			'Edited'          => 'edited interface message',
			'Declined'        => 'declined',
			'EditComment-c'   => 'edited a comment',
			'EditComment-r'   => 'edited a comment',
			'Unbanned'        => 'unbanned',
			'Promoted'        => 'promoted to tool admin',
			'BreakReserve'    => 'forcibly broke the reservation',
			'Prefchange'      => 'changed user preferences',
			'Renamed'         => 'renamed',
			'Demoted'         => 'demoted from tool admin',
			'ReceiveReserved' => 'received the reservation',
			'SendReserved'    => 'sent the reservation',
			'EditedEmail'     => 'edited email',
			'DeletedTemplate' => 'deleted template',
			'EditedTemplate'  => 'edited template',
			'CreatedEmail'    => 'created email',
			'CreatedTemplate' => 'created template',
			'SentMail'        => 'sent an email to the requestor',
			'Registered'      => 'registered a tool account',
		);

		if (array_key_exists($entry->getAction(), $lookup)) {
			return $lookup[$entry->getAction()];
		}

		// OK, I don't know what this is. Fall back to something sane.
		return "performed an unknown action ({$entry->getAction()})";
	}

	/**
	 * Summary of getLogs
	 *
	 * @param PdoDatabase $database
	 * @param string|null $userFilter
	 * @param string|null $actionFilter
	 * @param integer     $limit
	 * @param integer     $offset
	 *
	 * @return array|bool <string,string>
	 */
	public static function getLogs(PdoDatabase $database, $userFilter, $actionFilter, $limit = 100, $offset = 0)
	{
		$whereClause = "(:userFilter = 0 OR user = :userid) AND (:actionFilter = 0 OR action = :action)";
		$searchSqlStatement = "SELECT * FROM log WHERE $whereClause ORDER BY timestamp DESC LIMIT :limit OFFSET :offset;";
		$countSqlStatement = "SELECT COUNT(1) FROM log WHERE $whereClause;";

		$searchStatement = $database->prepare($searchSqlStatement);
		$countStatement = $database->prepare($countSqlStatement);

		$searchStatement->bindValue(":limit", $limit, PDO::PARAM_INT);
		$searchStatement->bindValue(":offset", $offset, PDO::PARAM_INT);

		if ($userFilter === null) {
			$searchStatement->bindValue(":userFilter", 0, PDO::PARAM_INT);
			$countStatement->bindValue(":userFilter", 0, PDO::PARAM_INT);
			$searchStatement->bindValue(":userid", 0, PDO::PARAM_INT);
			$countStatement->bindValue(":userid", 0, PDO::PARAM_INT);
		}
		else {
			$searchStatement->bindValue(":userFilter", 1, PDO::PARAM_INT);
			$countStatement->bindValue(":userFilter", 1, PDO::PARAM_INT);
			$searchStatement->bindValue(":userid", User::getByUsername($userFilter, $database)->getId(),
				PDO::PARAM_INT);
			$countStatement->bindValue(":userid", User::getByUsername($userFilter, $database)->getId(), PDO::PARAM_INT);
		}

		if ($actionFilter === null) {
			$searchStatement->bindValue(":actionFilter", 0, PDO::PARAM_INT);
			$countStatement->bindValue(":actionFilter", 0, PDO::PARAM_INT);
			$searchStatement->bindValue(":action", "", PDO::PARAM_STR);
			$countStatement->bindValue(":action", "", PDO::PARAM_STR);
		}
		else {
			$searchStatement->bindValue(":actionFilter", 1, PDO::PARAM_INT);
			$countStatement->bindValue(":actionFilter", 1, PDO::PARAM_INT);
			$searchStatement->bindValue(":action", $actionFilter, PDO::PARAM_STR);
			$countStatement->bindValue(":action", $actionFilter, PDO::PARAM_STR);
		}

		if (!$countStatement->execute()) {
			return false;
		}

		$count = $countStatement->fetchColumn(0);
		$countStatement->closeCursor();

		if ($searchStatement->execute()) {
			$data = $searchStatement->fetchAll(PDO::FETCH_CLASS, "Log");

			/** @var Log $entry */
			foreach ($data as $entry) {
				$entry->setDatabase($database);
				$entry->isNew = false;
			}

			$data['count'] = $count;

			return $data;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public static function getLogActions()
	{
		$database = gGetDb();

		$lookup = array(
			'Reserved'        => 'reserved',
			'Email Confirmed' => 'email-confirmed',
			'Unreserved'      => 'unreserved',
			'Approved'        => 'approved',
			'Suspended'       => 'suspended',
			'Banned'          => 'banned',
			'Edited'          => 'edited interface message',
			'Declined'        => 'declined',
			'EditComment-c'   => 'edited a comment',
			'EditComment-r'   => 'edited a comment',
			'Unbanned'        => 'unbanned',
			'Promoted'        => 'promoted to tool admin',
			'BreakReserve'    => 'forcibly broke the reservation',
			'Prefchange'      => 'changed user preferences',
			'Renamed'         => 'renamed',
			'Demoted'         => 'demoted from tool admin',
			'ReceiveReserved' => 'received the reservation',
			'SendReserved'    => 'sent the reservation',
			'EditedEmail'     => 'edited email',
			'DeletedTemplate' => 'deleted template',
			'EditedTemplate'  => 'edited template',
			'CreatedEmail'    => 'created email',
			'CreatedTemplate' => 'created template',
			'SentMail'        => 'sent an email to the requestor',
			'Registered'      => 'registered a tool account',
		);

		$statement = $database->query(<<<SQL
SELECT CONCAT('Closed ', id) AS k, CONCAT('closed (',name,')') AS v
FROM emailtemplate;
SQL
		);
		foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
			$lookup[$row['k']] = $row['v'];
		}

		return $lookup;
	}
	#endregion
}
