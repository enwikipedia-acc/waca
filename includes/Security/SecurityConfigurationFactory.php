<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

final class SecurityConfigurationFactory
{
	/**
	 * @var bool
	 */
	private $forceIdentified;

	/**
	 * Security constructor.
	 *
	 * @param bool $forceIdentified
	 */
	public function __construct($forceIdentified)
	{
		$this->forceIdentified = $forceIdentified;
	}

	/**
	 * Returns a pre-built security configuration for an internal page.
	 *
	 * @category Security-Critical
	 * @return SecurityConfiguration
	 */
	public function asInternalPage()
	{
		$config = new SecurityConfiguration();
		$config->setAdmin(SecurityConfiguration::ALLOW)
			->setUser(SecurityConfiguration::ALLOW);

		$config->setRequireIdentified($this->forceIdentified);

		return $config;
	}

	/**
	 * Returns a pre-built security configuration for a tool admin only page.
	 *
	 * @category Security-Critical
	 * @return SecurityConfiguration
	 */
	public function asAdminPage()
	{
		$config = new SecurityConfiguration();
		$config->setAdmin(SecurityConfiguration::ALLOW);

		$config->setRequireIdentified($this->forceIdentified);

		return $config;
	}

	/**
	 * Returns a pre-built security configuration for a page accessible to *ALL* logged in users, including suspended
	 * and new users. This probably isn't the setting you want.
	 *
	 * @category Security-Critical
	 * @return SecurityConfiguration
	 */
	public function asAllLoggedInUsersPage()
	{
		$config = new SecurityConfiguration();
		$config->setAdmin(SecurityConfiguration::ALLOW)
			->setUser(SecurityConfiguration::ALLOW)
			->setDeclined(SecurityConfiguration::ALLOW)
			->setNew(SecurityConfiguration::ALLOW)
			->setSuspended(SecurityConfiguration::ALLOW);

		$config->setRequireIdentified($this->forceIdentified);

		return $config;
	}

	/**
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public function asCheckUserData()
	{
		$config = new SecurityConfiguration();
		$config->setCheckuser(SecurityConfiguration::ALLOW)
			->setCommunity(SecurityConfiguration::DENY)
			->setSuspended(SecurityConfiguration::DENY)
			->setDeclined(SecurityConfiguration::DENY)
			->setNew(SecurityConfiguration::DENY);

		$config->setRequireIdentified($this->forceIdentified);

		return $config;
	}

	/**
	 * Returns a pre-built security configuration for a public page.
	 *
	 * @category Security-Critical
	 * @return SecurityConfiguration
	 */
	public function asPublicPage()
	{
		$config = new SecurityConfiguration();
		$config->setAdmin(SecurityConfiguration::ALLOW)
			->setUser(SecurityConfiguration::ALLOW)
			->setCheckuser(SecurityConfiguration::ALLOW)
			->setCommunity(SecurityConfiguration::ALLOW)
			->setSuspended(SecurityConfiguration::ALLOW)
			->setDeclined(SecurityConfiguration::ALLOW)
			->setNew(SecurityConfiguration::ALLOW);

		// Public pages shouldn't be inaccessible to logged-in, unidentified users.
		// Otherwise, logged in but unidentified users can't even log out.
		$config->setRequireIdentified(false);

		return $config;
	}

	/**
	 * Special case for zoom page private data.
	 *
	 * This will only return true if you are either a checkuser or a tool admin, taking special note of disabled
	 * accounts which happen to be check users
	 *
	 * @return SecurityConfiguration
	 */
	public function asGeneralPrivateDataAccess()
	{
		$config = new SecurityConfiguration();
		$config
			// Basic configuration, admins and check users allowed
			->setAdmin(SecurityConfiguration::ALLOW)
			->setCheckuser(SecurityConfiguration::ALLOW)
			// Deny these, even if they were allowed by the above
			->setCommunity(SecurityConfiguration::DENY)
			->setSuspended(SecurityConfiguration::DENY)
			->setDeclined(SecurityConfiguration::DENY)
			->setNew(SecurityConfiguration::DENY);

		// You must also be identified to access this data
		$config->setRequireIdentified($this->forceIdentified);

		return $config;
	}

	/**
	 * @category Security-Critical
	 * @return SecurityConfiguration
	 */
	public function asNone()
	{
		$config = new SecurityConfiguration();

		return $config;
	}
}