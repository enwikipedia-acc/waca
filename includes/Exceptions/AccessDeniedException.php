<?php

namespace Waca\Exceptions;

use Waca\DataObjects\Log;
use Waca\DataObjects\User;
use Waca\Helpers\Logger;
use Waca\PdoDatabase;

/**
 * Class AccessDeniedException
 *
 * Thrown when a logged-in user does not have permissions to access a page
 *
 * @package Waca\Exceptions
 */
class AccessDeniedException extends ReadableException
{
	public function getReadableError()
	{
		header("HTTP/1.1 403 Forbidden");

		$this->setUpSmarty();

		// uck. We should still be able to access the database in this situation though.
		$database = PdoDatabase::getDatabaseConnection('acc');
		$currentUser = User::getCurrent($database);
		$this->assign('currentUser', $currentUser);
		$this->assign("loggedIn", (!$currentUser->isCommunityUser()));

		if ($currentUser->isDeclined()) {
			$this->assign('htmlTitle', 'Account Declined');
			$this->assign('declineReason', $this->getLogEntry('Declined', $currentUser, $database));

			return $this->fetchTemplate("exception/account-declined.tpl");
		}

		if ($currentUser->isSuspended()) {
			$this->assign('htmlTitle', 'Account Suspended');
			$this->assign('suspendReason', $this->getLogEntry('Suspended', $currentUser, $database));

			return $this->fetchTemplate("exception/account-suspended.tpl");
		}

		if ($currentUser->isNewUser()) {
			$this->assign('htmlTitle', 'Account Pending');

			return $this->fetchTemplate("exception/account-new.tpl");
		}

		return $this->fetchTemplate("exception/access-denied.tpl");
	}

	private function getLogEntry($action, User $user, PdoDatabase $database)
	{
		/** @var Log[] $logs */
		list($logs, $count) = Logger::getLogs($database, null, $action, 'User', $user->getId(), 1);

		if ($count === false || count($logs) < 1) {
			return "Unable to retrieve log entry";
		}

		return $logs[0]->getComment();
	}
}