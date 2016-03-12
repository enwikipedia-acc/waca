<?php

namespace Waca\Pages;

use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Helpers\Logger;
use Waca\Security\SecurityConfiguration;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

/**
 * Class PageUserManagement
 * @package Waca\Pages
 */
class PageUserManagement extends InternalPageBase
{
	/** @var string */
	private $adminMailingList = 'enwiki-acc-admins@googlegroups.com';

	/**
	 * Main function for this page, when no specific actions are called.
	 */
	protected function main()
	{
		$this->setHtmlTitle('User Management');

		$database = $this->getDatabase();

		if (WebRequest::getBoolean("showAll")) {
			$this->assign("showAll", true);

			$this->assign("suspendedUsers", User::getAllWithStatus("Suspended", $database));
			$this->assign("declinedUsers", User::getAllWithStatus("Declined", $database));
		}
		else {
			$this->assign("showAll", false);
			$this->assign("suspendedUsers", array());
			$this->assign("declinedUsers", array());
		}

		$this->assign("newUsers", User::getAllWithStatus("New", $database));
		$this->assign("normalUsers", User::getAllWithStatus("User", $database));
		$this->assign("adminUsers", User::getAllWithStatus("Admin", $database));
		$this->assign("checkUsers", User::getAllCheckusers($database));

		$this->getTypeAheadHelper()->defineTypeAheadSource('username-typeahead', function() use ($database) {
			return User::getAllUsernames($database);
		});

		$this->setTemplate("usermanagement/main.tpl");
	}

	#region Access control

	/**
	 * Action target for suspending users
	 *
	 * @throws ApplicationLogicException
	 */
	protected function suspend()
	{
		$this->setHtmlTitle('User Management');

		$database = $this->getDatabase();

		$userId = WebRequest::getInt('user');

		/** @var User $user */
		$user = User::getById($userId, $database);

		if ($user === false) {
			throw new ApplicationLogicException('Sorry, the user you are trying to suspend could not be found.');
		}

		if ($user->isSuspended()) {
			throw new ApplicationLogicException('Sorry, the user you are trying to suspend is already suspended.');
		}

		// Dual-mode action
		if (WebRequest::wasPosted()) {
			$reason = WebRequest::postString('reason');

			if ($reason === null || trim($reason) === "") {
				throw new ApplicationLogicException('No reason provided');
			}

			$user->setStatus(User::STATUS_SUSPENDED);
			$user->setUpdateVersion(WebRequest::postInt('updateversion'));
			$user->save();
			Logger::suspendedUser($database, $user, $reason);

			$this->getNotificationHelper()->userSuspended($user, $reason);
			SessionAlert::quick('Suspended user ' . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'));

			// send email
			$this->sendStatusChangeEmail(
				'Your WP:ACC account has been suspended',
				'usermanagement/emails/suspended.tpl',
				$reason,
				$user,
				User::getCurrent($database)->getUsername()
			);

			$this->redirect('userManagement');

			return;
		}
		else {
			$this->setTemplate('usermanagement/changelevel-reason.tpl');
			$this->assign('user', $user);
			$this->assign('status', 'Suspended');
			$this->assign("showReason", true);
		}
	}

	/**
	 * Entry point for the decline action
	 *
	 * @throws ApplicationLogicException
	 */
	protected function decline()
	{
		$this->setHtmlTitle('User Management');

		$database = $this->getDatabase();

		$userId = WebRequest::getInt('user');
		$user = User::getById($userId, $database);

		if ($user === false) {
			throw new ApplicationLogicException('Sorry, the user you are trying to decline could not be found.');
		}

		if (!$user->isNew()) {
			throw new ApplicationLogicException('Sorry, the user you are trying to decline is not new.');
		}

		// Dual-mode action
		if (WebRequest::wasPosted()) {
			$reason = WebRequest::postString('reason');

			if ($reason === null || trim($reason) === "") {
				throw new ApplicationLogicException('No reason provided');
			}

			$user->setStatus(User::STATUS_DECLINED);
			$user->setUpdateVersion(WebRequest::postInt('updateversion'));
			$user->save();
			Logger::declinedUser($database, $user, $reason);

			$this->getNotificationHelper()->userDeclined($user, $reason);
			SessionAlert::quick('Declined user ' . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'));

			// send email
			$this->sendStatusChangeEmail(
				'Your WP:ACC account has been declined',
				'usermanagement/emails/declined.tpl',
				$reason,
				$user,
				User::getCurrent($database)->getUsername()
			);

			$this->redirect('userManagement');

			return;
		}
		else {
			$this->setTemplate('usermanagement/changelevel-reason.tpl');
			$this->assign('user', $user);
			$this->assign('status', 'Declined');
			$this->assign("showReason", true);
		}
	}

	/**
	 * Entry point for the demote action
	 *
	 * @throws ApplicationLogicException
	 */
	protected function demote()
	{
		$this->setHtmlTitle('User Management');

		$database = $this->getDatabase();

		$userId = WebRequest::getInt('user');
		$user = User::getById($userId, $database);

		if ($user === false) {
			throw new ApplicationLogicException('Sorry, the user you are trying to demote could not be found.');
		}

		if (!$user->isAdmin()) {
			throw new ApplicationLogicException('Sorry, the user you are trying to demote is not an admin.');
		}

		// Dual-mode action
		if (WebRequest::wasPosted()) {
			$reason = WebRequest::postString('reason');

			if ($reason === null || trim($reason) === "") {
				throw new ApplicationLogicException('No reason provided');
			}

			$user->setStatus(User::STATUS_USER);
			$user->setUpdateVersion(WebRequest::postInt('updateversion'));
			$user->save();
			Logger::demotedUser($database, $user, $reason);

			$this->getNotificationHelper()->userDemoted($user, $reason);
			SessionAlert::quick('Demoted user ' . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'));

			// send email
			$this->sendStatusChangeEmail(
				'Your WP:ACC account has been demoted',
				'usermanagement/emails/demoted.tpl',
				$reason,
				$user,
				User::getCurrent($database)->getUsername()
			);

			$this->redirect('userManagement');

			return;
		}
		else {
			$this->setTemplate('usermanagement/changelevel-reason.tpl');
			$this->assign('user', $user);
			$this->assign('status', 'User');
			$this->assign("showReason", true);
		}
	}

	/**
	 * Entry point for the approve action
	 *
	 * @throws ApplicationLogicException
	 */
	protected function approve()
	{
		$this->setHtmlTitle('User Management');

		$database = $this->getDatabase();

		$userId = WebRequest::getInt('user');
		$user = User::getById($userId, $database);

		if ($user === false) {
			throw new ApplicationLogicException('Sorry, the user you are trying to approve could not be found.');
		}

		if ($user->isUser() || $user->isAdmin()) {
			throw new ApplicationLogicException('Sorry, the user you are trying to approve is already an active user.');
		}

		// Dual-mode action
		if (WebRequest::wasPosted()) {
			$user->setStatus(User::STATUS_USER);
			$user->setUpdateVersion(WebRequest::postInt('updateversion'));
			$user->save();
			Logger::approvedUser($database, $user);

			$this->getNotificationHelper()->userApproved($user);
			SessionAlert::quick('Approved user ' . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'));

			// send email
			$this->sendStatusChangeEmail(
				'Your WP:ACC account has been approved',
				'usermanagement/emails/approved.tpl',
				null,
				$user,
				User::getCurrent($database)->getUsername()
			);

			$this->redirect("userManagement");

			return;
		}
		else {
			$this->setTemplate("usermanagement/changelevel-reason.tpl");
			$this->assign("user", $user);
			$this->assign("status", "User");
			$this->assign("showReason", false);
		}
	}

	/**
	 * Entry point for the promote action
	 *
	 * @throws ApplicationLogicException
	 */
	protected function promote()
	{
		$this->setHtmlTitle('User Management');

		$database = $this->getDatabase();

		$userId = WebRequest::getInt('user');
		$user = User::getById($userId, $database);

		if ($user === false) {
			throw new ApplicationLogicException('Sorry, the user you are trying to promote could not be found.');
		}

		if ($user->isAdmin()) {
			throw new ApplicationLogicException('Sorry, the user you are trying to promote is already an admin.');
		}

		// Dual-mode action
		if (WebRequest::wasPosted()) {
			$user->setStatus(User::STATUS_ADMIN);
			$user->setUpdateVersion(WebRequest::postInt('updateversion'));
			$user->save();
			Logger::promotedUser($database, $user);

			$this->getNotificationHelper()->userPromoted($user);
			SessionAlert::quick('Promoted user ' . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'));

			// send email
			$this->sendStatusChangeEmail(
				'Your WP:ACC account has been promoted',
				'usermanagement/emails/promoted.tpl',
				null,
				$user,
				User::getCurrent($database)->getUsername()
			);

			$this->redirect("userManagement");

			return;
		}
		else {
			$this->setTemplate("usermanagement/changelevel-reason.tpl");
			$this->assign("user", $user);
			$this->assign("status", "Admin");
			$this->assign("showReason", false);
		}
	}

	#endregion

	#region Renaming / Editing

	/**
	 * Entry point for the rename action
	 *
	 * @throws ApplicationLogicException
	 */
	protected function rename()
	{
		$this->setHtmlTitle('User Management');

		$database = $this->getDatabase();

		$userId = WebRequest::getInt('user');
		$user = User::getById($userId, $database);

		if ($user === false) {
			throw new ApplicationLogicException('Sorry, the user you are trying to rename could not be found.');
		}

		// Dual-mode action
		if (WebRequest::wasPosted()) {
			$newUsername = WebRequest::postString('newname');

			if ($newUsername === null || trim($newUsername) === "") {
				throw new ApplicationLogicException('The new username cannot be empty');
			}

			if (User::getByUsername($newUsername, $database) != false) {
				throw new ApplicationLogicException('The new username already exists');
			}

			$oldUsername = $user->getUsername();
			$user->setUsername($newUsername);
			$user->setUpdateVersion(WebRequest::postInt('updateversion'));

			$user->save();

			$logEntryData = serialize(array(
				'old' => $oldUsername,
				'new' => $newUsername,
			));

			Logger::renamedUser($database, $user, $logEntryData);

			SessionAlert::quick("Changed User "
				. htmlentities($oldUsername, ENT_COMPAT, 'UTF-8')
				. " name to "
				. htmlentities($newUsername, ENT_COMPAT, 'UTF-8'));

			$this->getNotificationHelper()->userRenamed($user, $oldUsername);

			// send an email to the user.
			$this->assign('targetUsername', $user->getUsername());
			$this->assign('toolAdmin', User::getCurrent($database)->getUsername());
			$this->assign('oldUsername', $oldUsername);
			$this->assign('mailingList', $this->adminMailingList);

			$this->getEmailHelper()->sendMail(
				$user->getEmail(),
				'Your username on WP:ACC has been changed',
				$this->fetchTemplate('usermanagement/emails/renamed.tpl'),
				array('Reply-To' => $this->adminMailingList)
			);

			$this->redirect("userManagement");

			return;
		}
		else {
			$this->setTemplate('usermanagement/renameuser.tpl');
			$this->assign('user', $user);
		}
	}

	/**
	 * Entry point for the edit action
	 *
	 * @throws ApplicationLogicException
	 */
	protected function editUser()
	{
		$this->setHtmlTitle('User Management');

		$database = $this->getDatabase();

		$userId = WebRequest::getInt('user');
		$user = User::getById($userId, $database);

		if ($user === false) {
			throw new ApplicationLogicException('Sorry, the user you are trying to edit could not be found.');
		}

		// Dual-mode action
		if (WebRequest::wasPosted()) {
			$newEmail = WebRequest::postEmail('user_email');
			$newOnWikiName = WebRequest::postString('user_onwikiname');

			if ($newEmail === null) {
				throw new ApplicationLogicException('Invalid email address');
			}

			if (!$user->isOAuthLinked()) {
				if (trim($newOnWikiName) == "") {
					throw new ApplicationLogicException('New on-wiki username cannot be blank');
				}

				$user->setOnWikiName($newOnWikiName);
			}

			$user->setEmail($newEmail);

			$user->setUpdateVersion(WebRequest::postInt('updateversion'));

			$user->save();

			Logger::userPreferencesChange($database, $user);
			$this->getNotificationHelper()->userPrefChange($user);
			SessionAlert::quick('Changes to user\'s preferences have been saved');

			$this->redirect("userManagement");

			return;
		}
		else {
			$this->setTemplate('usermanagement/edituser.tpl');
			$this->assign('user', $user);
		}
	}

	#endregion

	/**
	 * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
	 * the return value from this function.
	 *
	 * If this page even supports actions, you will need to check the route
	 *
	 * @return \Waca\Security\SecurityConfiguration
	 * @category Security-Critical
	 */
	protected function getSecurityConfiguration()
	{
		return SecurityConfiguration::adminPage();
	}

	/**
	 * Sends a status change email to the user.
	 *
	 * @param string      $subject           The subject of the email
	 * @param string      $template          The smarty template to use
	 * @param string|null $reason            The reason for performing the status change
	 * @param User        $user              The user affected
	 * @param string      $toolAdminUsername The tool admin's username who is making the edit
	 */
	private function sendStatusChangeEmail($subject, $template, $reason, $user, $toolAdminUsername)
	{
		$this->assign('targetUsername', $user->getUsername());
		$this->assign('toolAdmin', $toolAdminUsername);
		$this->assign('actionReason', $reason);
		$this->assign('mailingList', $this->adminMailingList);

		$this->getEmailHelper()->sendMail(
			$user->getEmail(),
			$subject,
			$this->fetchTemplate($template),
			array('Reply-To' => $this->adminMailingList)
		);
	}
}