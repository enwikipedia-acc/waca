<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

/**
 * Created by PhpStorm.
 * User: stwalkerster
 * Date: 26/03/2016
 * Time: 02:55
 */

namespace Waca\Helpers;

use Exception;
use PDO;
use Waca\DataObject;
use Waca\DataObjects\Ban;
use Waca\DataObjects\Comment;
use Waca\DataObjects\EmailTemplate;
use Waca\DataObjects\Log;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\DataObjects\WelcomeTemplate;
use Waca\PdoDatabase;
use Waca\SiteConfiguration;

class LogHelper
{
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
			$data = $logStatement->fetchAll(PDO::FETCH_CLASS, Log::class);

			/** @var Log $entry */
			foreach ($data as $entry) {
				$entry->setDatabase($db);
			}

			return $data;
		}

		return false;
	}

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

		/**
		 * @param DataObject $item
		 *
		 * @return int
		 */
		$sortKey = function(DataObject $item) {
			if ($item instanceof Log) {
				return $item->getTimestamp()->getTimestamp();
			}

			if ($item instanceof Comment) {
				return $item->getTime()->getTimestamp();
			}

			return 0;
		};

		do {
			$flag = false;

			$loopLimit = (count($items) - 1);
			for ($i = 0; $i < $loopLimit; $i++) {
				// are these two items out of order?
				if ($sortKey($items[$i]) > $sortKey($items[$i + 1])) {
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
	 * @param PdoDatabase $database
	 *
	 * @return array
	 */
	public static function getLogActions(PdoDatabase $database)
	{
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

	/**
	 * Summary of getLogs
	 *
	 * @param PdoDatabase  $database
	 * @param string|null  $userFilter
	 * @param string|null  $actionFilter
	 * @param string|null  $objectTypeFilter
	 * @param integer|null $objectFilter
	 * @param integer      $limit
	 * @param integer      $offset
	 *
	 * @return array
	 */
	public static function getLogs(
		PdoDatabase $database,
		$userFilter,
		$actionFilter,
		$objectTypeFilter = null,
		$objectFilter = null,
		$limit = 100,
		$offset = 0
	) {
		$whereClause = <<<TXT
(:userFilter = 0 OR user = :userid)
AND (:actionFilter = 0 OR action = :action)
AND (:objectFilter = 0 OR objectid = :object)
AND (:objectTypeFilter = 0 OR objecttype = :objectType)
TXT;
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

		if ($objectTypeFilter === null) {
			$searchStatement->bindValue(":objectTypeFilter", 0, PDO::PARAM_INT);
			$countStatement->bindValue(":objectTypeFilter", 0, PDO::PARAM_INT);
			$searchStatement->bindValue(":objectType", "", PDO::PARAM_STR);
			$countStatement->bindValue(":objectType", "", PDO::PARAM_STR);
		}
		else {
			$searchStatement->bindValue(":objectTypeFilter", 1, PDO::PARAM_INT);
			$countStatement->bindValue(":objectTypeFilter", 1, PDO::PARAM_INT);
			$searchStatement->bindValue(":objectType", $objectTypeFilter, PDO::PARAM_STR);
			$countStatement->bindValue(":objectType", $objectTypeFilter, PDO::PARAM_STR);
		}

		if ($objectFilter === null) {
			$searchStatement->bindValue(":objectFilter", 0, PDO::PARAM_INT);
			$countStatement->bindValue(":objectFilter", 0, PDO::PARAM_INT);
			$searchStatement->bindValue(":object", "", PDO::PARAM_STR);
			$countStatement->bindValue(":object", "", PDO::PARAM_STR);
		}
		else {
			$searchStatement->bindValue(":objectFilter", 1, PDO::PARAM_INT);
			$countStatement->bindValue(":objectFilter", 1, PDO::PARAM_INT);
			$searchStatement->bindValue(":object", $objectFilter, PDO::PARAM_INT);
			$countStatement->bindValue(":object", $objectFilter, PDO::PARAM_INT);
		}

		if (!$countStatement->execute()) {
			return array(false, false);
		}

		$count = $countStatement->fetchColumn(0);
		$countStatement->closeCursor();

		if ($searchStatement->execute()) {
			$data = $searchStatement->fetchAll(PDO::FETCH_CLASS, Log::class);

			/** @var Log $entry */
			foreach ($data as $entry) {
				$entry->setDatabase($database);
			}

			return array($data, $count);
		}

		return array(false, false);
	}

	/**
	 * This returns a HTML
	 *
	 * @param string            $objectId
	 * @param string            $objectType
	 * @param PdoDatabase       $database
	 * @param SiteConfiguration $configuration
	 *
	 * @return null|string
	 * @category Security-Critical
	 */
	private static function getObjectDescription($objectId, $objectType, PdoDatabase $database, SiteConfiguration $configuration)
	{
		if ($objectType == '') {
			return null;
		}

		$baseurl = $configuration->getBaseUrl();

		switch ($objectType) {
			case 'Ban':
				/** @var Ban $ban */
				$ban = Ban::getById($objectId, $database);

				return 'Ban #' . $objectId . " (" . htmlentities($ban->getTarget()) . ")</a>";
			case 'EmailTemplate':
				/** @var EmailTemplate $emailTemplate */
				$emailTemplate = EmailTemplate::getById($objectId, $database);
				$name = htmlentities($emailTemplate->getName(), ENT_COMPAT, 'UTF-8');

				return <<<HTML
<a href="{$baseurl}/internal.php/emailManagement/view?id={$objectId}">Email Template #{$objectId} ({$name})</a>
HTML;
			case 'SiteNotice':
				return "<a href=\"{$baseurl}/internal.php/siteNotice\">the site notice</a>";
			case 'Request':
				/** @var Request $request */
				$request = Request::getById($objectId, $database);
				$name = htmlentities($request->getName(), ENT_COMPAT, 'UTF-8');

				return <<<HTML
<a href="{$baseurl}/internal.php/viewRequest?id={$objectId}">Request #{$objectId} ({$name})</a>
HTML;
			case 'User':
				/** @var User $user */
				$user = User::getById($objectId, $database);
				$username = htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8');

				return "<a href=\"{$baseurl}/internal.php/statistics/users/detail?user={$objectId}\">{$username}</a>";
			case 'WelcomeTemplate':
				/** @var WelcomeTemplate $welcomeTemplate */
				$welcomeTemplate = WelcomeTemplate::getById($objectId, $database);
				$userCode = htmlentities($welcomeTemplate->getUserCode(), ENT_COMPAT, 'UTF-8');

				return "<a href=\"{$baseurl}/internal.php/welcomeTemplates/view?id={$objectId}\">{$userCode}</a>";
			default:
				return '[' . $objectType . " " . $objectId . ']';
		}
	}

	/**
	 * @param    Log[]          $logs
	 * @param     PdoDatabase   $database
	 * @param SiteConfiguration $configuration
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function prepareLogsForTemplate($logs, PdoDatabase $database, SiteConfiguration $configuration)
	{
		$userIds = array();

		/** @var Log $logEntry */
		foreach ($logs as $logEntry) {
			if (!$logEntry instanceof Log) {
				// if this happens, we've done something wrong with passing back the log data.
				throw new Exception('Log entry is not an instance of a Log, this should never happen.');
			}

			$user = $logEntry->getUser();
			if ($user === -1) {
				continue;
			}

			if (!array_search($user, $userIds)) {
				$userIds[] = $user;
			}
		}

		$users = User::getUsernames($userIds, $database);
		$users[-1] = User::getCommunity()->getUsername();

		$logData = array();

		/** @var Log $logEntry */
		foreach ($logs as $logEntry) {
			$objectDescription = self::getObjectDescription($logEntry->getObjectId(), $logEntry->getObjectType(),
				$database, $configuration);

			if($logEntry->getAction() === 'Renamed'){
				$renameData = unserialize($logEntry->getComment());
				$oldName = htmlentities($renameData['old'], ENT_COMPAT, 'UTF-8');
				$newName = htmlentities($renameData['new'], ENT_COMPAT, 'UTF-8');
				$comment = 'Renamed \'' . $oldName . '\' to \'' . $newName . '\'.';
			}
			else{
				$comment = $logEntry->getComment();
			}

			$logData[] = array(
				'timestamp'         => $logEntry->getTimestamp(),
				'userid'            => $logEntry->getUser(),
				'username'          => $users[$logEntry->getUser()],
				'description'       => self::getLogDescription($logEntry),
				'objectdescription' => $objectDescription,
				'comment'           => $comment,
			);
		}

		return array($users, $logData);
	}
}