<?php

namespace Waca\Pages;

use SessionAlert;
use User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PageBase;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class PagePreferences extends PageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 * @return void
	 */
	protected function main()
	{
		$enforceOAuth = $this->getSiteConfiguration()->getEnforceOAuth();

		// Dual mode
		if (WebRequest::wasPosted()) {
			$user = User::getCurrent();
			$user->setWelcomeSig(WebRequest::postString('sig'));
			$user->setEmailSig(WebRequest::postString('emailsig'));
			$user->setAbortPref(WebRequest::getBoolean('sig') ? 1 : 0);

			$email = WebRequest::postEmail('email');
			if ($email !== null) {
				$user->setEmail($email);
			}

			$user->save();
			SessionAlert::success("Preferences updated!");

			$this->redirect('');
		}
		else {
			$this->setTemplate('preferences/prefs.tpl');
			$this->assign("enforceOAuth", $enforceOAuth);
		}
	}

	protected function changePassword()
	{
		if (WebRequest::wasPosted()) {
			$oldPassword = WebRequest::postString('oldpassword');
			$newPassword = WebRequest::postString('newpassword');
			$newPasswordConfirmation = WebRequest::postString('newpasswordconfirm');

			$user = User::getCurrent($this->getDatabase());

			$this->validateNewPassword($oldPassword, $newPassword, $newPasswordConfirmation, $user);

			$user->setPassword($newPassword);
			$user->save();

			SessionAlert::success('Password changed successfully!');

			$this->redirect('preferences');
		}
		else {
			// not allowed to GET this.
			$this->redirect('preferences');
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
	 * @todo     Verify the security config here - do we want this internal only, or allow all logged in users access?
	 */
	protected function getSecurityConfiguration()
	{
		return SecurityConfiguration::allLoggedInUsersPage();
	}

	/**
	 * @param $oldPassword
	 * @param $newPassword
	 * @param $newPasswordConfirmation
	 * @param $user
	 *
	 * @throws ApplicationLogicException
	 */
	protected function validateNewPassword($oldPassword, $newPassword, $newPasswordConfirmation, $user)
	{
		if ($oldPassword === null || $newPassword === null || $newPasswordConfirmation === null) {
			throw new ApplicationLogicException('All three fields must be completed to change your password');
		}

		if ($newPassword !== $newPasswordConfirmation) {
			throw new ApplicationLogicException('Your new passwords did not match!');
		}

		if (!$user->authenticate($oldPassword)) {
			throw new ApplicationLogicException('The password you entered was incorrect.');
		}
	}
}