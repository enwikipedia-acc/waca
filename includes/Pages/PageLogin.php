<?php

namespace Waca\Pages;

use User;
use Waca\Exceptions\ApplicationLogicException;
use Waca\SecurityConfiguration;
use Waca\Tasks\InternalPageBase;
use Waca\WebRequest;

/**
 * Class PageLogin
 * @package Waca\Pages
 */
class PageLogin extends InternalPageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 */
	protected function main()
	{
		// Start by enforcing HTTPS
		if ($this->getSiteConfiguration()->getUseStrictTransportSecurity() !== false) {
			if (WebRequest::isHttps()) {
				// Client can clearly use HTTPS, so let's enforce it for all connections.
				header("Strict-Transport-Security: max-age=15768000");
			}
			else {
				// This is the login form, not the request form. We need protection here.
				$this->redirectUrl('https://' . WebRequest::serverName() . WebRequest::requestUri());

				return;
			}
		}

		if (WebRequest::wasPosted()) {
			// POST. Do some authentication.

			$user = $this->getAuthenticatingUser();

			// Touch force logout
			$user->setForcelogout(false);
			$user->save();

			if ($this->getSiteConfiguration()->getEnforceOAuth()) {
				if (!$user->isOAuthLinked()) {
					$oauthHelper = $this->getOAuthHelper();

					$requestToken = $oauthHelper->getRequestToken();
					$user->setOAuthRequestToken($requestToken->key);
					$user->setOAuthRequestSecret($requestToken->secret);
					$user->save();

					WebRequest::setPartialLogin($user);
					$this->redirectUrl($oauthHelper->getAuthoriseUrl($requestToken->key));

					return;
				}
			}

			WebRequest::setLoggedInUser($user);

			$this->goBackWhenceYouCame($user);
		}
		else {
			// GET. Show the form
			$this->setTemplate("login.tpl");
		}
	}

	/**
	 * @return User
	 * @throws ApplicationLogicException
	 */
	private function getAuthenticatingUser()
	{
		$username = WebRequest::postString("username");
		$password = WebRequest::postString("password");

		if ($username === null || $password === null || $username === "" || $password === "") {
			throw new ApplicationLogicException("No username/password specified");
		}

		$user = User::getByUsername($username, $this->getDatabase());

		if ($user == false || !$user->authenticate($password)) {
			throw new ApplicationLogicException("Authentication failed");
		}

		return $user;
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
		// Login pages, by definition, have to be accessible to the public
		return SecurityConfiguration::publicPage();
	}

	/**
	 * Redirect the user back to wherever they came from after a successful login
	 *
	 * @param User $user
	 */
	private function goBackWhenceYouCame(User $user)
	{
		// Redirect to wherever the user came from
		$redirectDestination = WebRequest::clearPostLoginRedirect();
		if ($redirectDestination !== null) {
			$this->redirectUrl($redirectDestination);
		}
		else {
			if ($user->isNew()) {
				// home page isn't allowed, go to preferences instead
				$this->redirect('preferences');
			}
			else {
				// go to the home page
				$this->redirect('');
			}
		}
	}
}