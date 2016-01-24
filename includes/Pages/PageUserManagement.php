<?php

namespace Waca\Pages;

use Notification;
use SessionAlert;
use User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PageBase;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class PageUserManagement extends PageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 */
	protected function main()
	{
		$database = gGetDb();

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

		$this->setTailScript(\getTypeaheadSource(User::getAllUsernames($database)));

		$this->setTemplate("usermanagement/main.tpl");
	}

	/**
	 * Action target for suspending users
	 *
	 * @throws ApplicationLogicException
	 */
	protected function suspend()
	{
		$database = gGetDb();

		$userId = WebRequest::getInt('user');
		$user = User::getById($userId, $database);

		if($user === false){
			throw new ApplicationLogicException('Sorry, the user you are trying to suspend could not be found.');
		}

		if($user->isSuspended()){
			throw new ApplicationLogicException('Sorry, the user you are trying to suspend is already suspended.');
		}

		// Dual-mode action
		if(WebRequest::wasPosted()){
			$reason = WebRequest::postString('reason');

			if($reason === null || trim($reason) === ""){
				throw new ApplicationLogicException('No reason provided');
			}

			$user->suspend($reason);

			Notification::userSuspended($user, $reason);
			SessionAlert::quick('Suspended user ' . htmlentities($user->getUsername(), ENT_COMPAT, 'UTF-8'));

			// TODO: send email

			$this->redirect("userManagement");
			return;
		}
		else {
			$this->setTemplate("usermanagement/changelevel-reason.tpl");
			$this->assign("user", $user);
			$this->assign("status", "Suspended");
		}
	}

	/**
	 * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
	 * the return value from this function.
	 *
	 * If this page even supports actions, you will need to check the route
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	protected function getSecurityConfiguration()
	{
		return SecurityConfiguration::adminPage();
	}
}