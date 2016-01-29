<?php

namespace Waca\Pages;

use PdoDatabase;
use SessionAlert;
use User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\PageBase;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class PageForgotPassword extends PageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 *
	 * This is the forgotten password reset form
	 * @category Security-Critical
	 */
	protected function main()
	{
		if (WebRequest::wasPosted()) {
			$username = WebRequest::postString('username');
			$email = WebRequest::postEmail('email');
			$database = gGetDb();

			if ($username === null || trim($username) === "" || $email === null || trim($email) === "") {
				throw new ApplicationLogicException("Both username and email address must be specified!");
			}

			$user = User::getByUsername($username, $database);

			// If the user isn't found, or the email address is wrong, skip sending the details silently.
			if ($user !== false && strtolower($user->getEmail()) === strtolower($email)) {
				$clientIp = \getTrustedClientIP(WebRequest::remoteAddress(), WebRequest::forwardedAddress());

				$this->assign("user", $user);
				$this->assign("hash", $user->getForgottenPasswordHash());
				$this->assign("remoteAddress", $clientIp);

				$emailContent = $this->fetchTemplate('forgot-password/reset-mail.tpl');

				$this->getEmailHelper()->sendMail($user->getEmail(), "", $emailContent);
			}

			SessionAlert::success(
				'<strong>Your password reset request has been completed.</strong> Please check your e-mail.');

			$this->redirect('login');
		}
		else {
			$this->setTemplate('forgot-password/forgotpw.tpl');
		}
	}

	/**
	 * Entry point for the reset action
	 *
	 * This is the reset password part of the form.
	 * @category Security-Critical
	 */
	protected function reset()
	{
		$si = WebRequest::getString('si');
		$id = WebRequest::getString('id');

		if ($si === null || trim($si) === "" || $id === null || trim($id) === "") {
			throw new ApplicationLogicException("Link not valid, please ensure it has copied correctly");
		}

		$database = gGetDb();
		$user = $this->getResettingUser($id, $database, $si);

		// Dual mode
		if (WebRequest::wasPosted()) {
			$this->doReset($user);
		}
		else {
			$this->assign('user', $user);
			$this->setTemplate('forgot-password/forgotpwreset.tpl');
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
		return SecurityConfiguration::publicPage();
	}

	/**
	 * Gets the user resetting their password from the database, or throwing an exception if that is not possible.
	 *
	 * @param integer     $id       The ID of the user to retrieve
	 * @param PdoDatabase $database The database object to use
	 * @param string      $si       The reset hash provided
	 * @return User
	 * @throws ApplicationLogicException
	 */
	private function getResettingUser($id, $database, $si)
	{
		$user = User::getById($id, $database);

		if ($user === false || $user->getForgottenPasswordHash() !== $si || $user->isCommunityUser()) {
			throw new ApplicationLogicException("User not found");
		}

		return $user;
	}

	/**
	 * Performs the setting of the new password
	 *
	 * @param User $user The user to set the password for
	 * @throws ApplicationLogicException
	 */
	private function doReset(User $user)
	{
		$pw = WebRequest::postString('pw');
		$pw2 = WebRequest::postString('pw2');

		if ($pw === $pw2) {
			$user->setPassword($pw);
			$user->save();

			SessionAlert::success('You may now log in!');
			$this->redirect('login');
		}
		else {
			throw new ApplicationLogicException('Passwords do not match!');
		}
	}
}