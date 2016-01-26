<?php

namespace Waca\Pages;

use Exception;
use User;
use Waca\PageBase;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

/**
 * Class PageLogin
 * @package Waca\Pages
 */
class PageLogin extends PageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 */
	protected function main()
	{
		// Start by enforcing HTTPS
		global $strictTransportSecurityExpiry;
		if ($strictTransportSecurityExpiry !== false) {
			if (WebRequest::isHttps()) {
				// Client can clearly use HTTPS, so let's enforce it for all connections.
				header("Strict-Transport-Security: max-age=15768000");
			}
			else {
				// This is the login form, not the request form. We need protection here.
				$this->redirectUrl('https://' . WebRequest::getServerName() . WebRequest::getRequestUri());
				return;
			}
		}

		if (WebRequest::wasPosted()) {
			// POST. Do some authentication.
			$username = WebRequest::postString("username");
			$password = WebRequest::postString("password");

			if ($username === null || $password === null || $username === "" || $password === "") {
				// todo: no username/password submitted.
				throw new Exception("No username/password specified");
			}

			$user = User::getByUsername($username, gGetDb());

			if ($user == false || !$user->authenticate($password)) {
				// todo: authentication failed
				throw new Exception("Authentication failed");
			}

			// Touch force logout
			$user->setForcelogout(0);
			$user->save();

			// TODO: OAuth code

			WebRequest::setLoggedInUser($user);

			// Redirect to the main page
			$this->redirect("");
		}
		else {
			// GET. Show the form
			$this->setTemplate("login.tpl");
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
		// Login pages, by definition, have to be accessible to the public
		return SecurityConfiguration::publicPage();
	}
}