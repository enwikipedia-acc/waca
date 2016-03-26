<?php

namespace Waca\Pages;

use Waca\DataObjects\User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\Security\SecurityConfiguration;
use Waca\SessionAlert;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

class PagePreferences extends InternalPageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 * @return void
	 */
	protected function main()
	{
		$this->setHtmlTitle('Preferences');

		$enforceOAuth = $this->getSiteConfiguration()->getEnforceOAuth();

		// Dual mode
		if (WebRequest::wasPosted()) {
			$this->validateCSRFToken();
			$user = User::getCurrent($this->getDatabase());
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
			$this->assignCSRFToken();
			$this->setTemplate('preferences/prefs.tpl');
			$this->assign("enforceOAuth", $enforceOAuth);
		}
	}

	protected function changePassword()
	{
		$this->setHtmlTitle('Change Password');

		if (WebRequest::wasPosted()) {
			$this->validateCSRFToken();
			$oldPassword = WebRequest::postString('oldpassword');
			$newPassword = WebRequest::postString('newpassword');
			$newPasswordConfirmation = WebRequest::postString('newpasswordconfirm');

			$user = User::getCurrent($this->getDatabase());
			if (!$user instanceof User) {
				throw new ApplicationLogicException('User not found');
			}

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
	 */
	protected function getSecurityConfiguration()
	{
		return $this->getSecurityManager()->configure()->asInternalPage();
	}

	/**
	 * @param string $oldPassword
	 * @param string $newPassword
	 * @param string $newPasswordConfirmation
	 * @param User   $user
	 *
	 * @throws ApplicationLogicException
	 */
	protected function validateNewPassword($oldPassword, $newPassword, $newPasswordConfirmation, User $user)
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