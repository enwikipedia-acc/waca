<?php

namespace Waca;

use Waca\DataObjects\User;
use Waca\Exceptions\AccessDeniedException;

/**
 * Class SecurityConfiguration
 * @package  Waca
 * @category Security-Critical
 */
final class SecurityConfiguration
{
	const ALLOW = "allow";
	const DENY = "deny";
	private $admin = "default";
	private $user = "default";
	private $checkuser = "default";
	private $community = "default";
	private $suspended = "default";
	private $declined = "default";
	private $new = "default";
	private $requireIdentified;

	/**
	 * SecurityConfiguration constructor.
	 */
	public function __construct()
	{
		global $forceIdentification;

		// Initialise require identified to the boolean value of $forceIdentification. Test for truthiness not true
		// because I think we set this to 1/0 instead of true/false.
		$this->requireIdentified = ($forceIdentification == 1);
	}

	/**
	 * @param string $admin
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public function setAdmin($admin)
	{
		$this->admin = $admin;

		return $this;
	}

	/**
	 * @param string $user
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public function setUser($user)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Sets whether a checkuser is able to gain access.
	 *
	 * This is private because it's DANGEROUS. Checkusers are not mutually-exclusive with other rights. As such, a
	 * suspended checkuser who tries to access a page which allows checkusers will be granted access to the page, UNLESS
	 * that page is also set to deny New/Declined/Suspended users. I have no problem with this method being used, but
	 * please ONLY use it in this class in static methods. DO NOT set it to public.
	 *
	 * @param string $checkuser
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	private function setCheckuser($checkuser)
	{
		$this->checkuser = $checkuser;

		return $this;
	}

	/**
	 * @param string $community
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public function setCommunity($community)
	{
		$this->community = $community;

		return $this;
	}

	/**
	 * @param string $suspended
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public function setSuspended($suspended)
	{
		$this->suspended = $suspended;

		return $this;
	}

	/**
	 * @param string $declined
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public function setDeclined($declined)
	{
		$this->declined = $declined;

		return $this;
	}

	/**
	 * @param string $new
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public function setNew($new)
	{
		$this->new = $new;

		return $this;
	}

	/**
	 * Tests if a user is allowed to perform an action.
	 *
	 * This method should form a hard, deterministic security barrier, and only return true if it is absolutely sure
	 * that a user should have access to something.
	 *
	 * @param User $user
	 *
	 * @return bool
	 * @category Security-Critical
	 */
	public function allows(User $user)
	{
		$allowed = false;

		if ($this->requireIdentified && !$user->isCommunityUser() && !$user->isIdentified()) {
			return false;
		}

		try {
			$allowed = $this->test($this->admin, $user->isAdmin())
				|| $this->test($this->user, $user->isUser())
				|| $this->test($this->community, $user->isCommunityUser())
				|| $this->test($this->suspended, $user->isSuspended())
				|| $this->test($this->declined, $user->isDeclined())
				|| $this->test($this->new, $user->isNew())
				|| $this->test($this->checkuser, $user->isCheckuser());

			return $allowed;
		}
		catch (AccessDeniedException $ex) {
			// something is set to deny.
			return false;
		}
	}

	/**
	 * Returns a pre-built security configuration for a public page.
	 *
	 * @category Security-Critical
	 * @return SecurityConfiguration
	 */
	public static function publicPage()
	{
		$config = new SecurityConfiguration();
		$config->setAdmin(self::ALLOW)
			->setUser(self::ALLOW)
			->setCheckuser(self::ALLOW)
			->setCommunity(self::ALLOW)
			->setSuspended(self::ALLOW)
			->setDeclined(self::ALLOW)
			->setNew(self::ALLOW);

		// Public pages shouldn't be inaccessible to logged-in, unidentified users.
		// Otherwise, logged in but unidentified users can't even log out.
		$config->requireIdentified = false;

		return $config;
	}

	/**
	 * Returns a pre-built security configuration for an internal page.
	 *
	 * @category Security-Critical
	 * @return SecurityConfiguration
	 */
	public static function internalPage()
	{
		$config = new SecurityConfiguration();
		$config->setAdmin(self::ALLOW)->setUser(self::ALLOW);

		return $config;
	}

	/**
	 * Returns a pre-built security configuration for a tool admin only page.
	 *
	 * @category Security-Critical
	 * @return SecurityConfiguration
	 */
	public static function adminPage()
	{
		$config = new SecurityConfiguration();
		$config->setAdmin(self::ALLOW);

		return $config;
	}

	/**
	 * Returns a pre-built security configuration for a page accessible to *ALL* logged in users, including suspended
	 * and new users. This probably isn't the setting you want.
	 *
	 * @category Security-Critical
	 * @return SecurityConfiguration
	 */
	public static function allLoggedInUsersPage()
	{
		$config = new SecurityConfiguration();
		$config->setAdmin(self::ALLOW)
			->setUser(self::ALLOW)
			->setDeclined(self::ALLOW)
			->setNew(self::ALLOW)
			->setSuspended(self::ALLOW);

		return $config;
	}

	/**
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public static function checkUserData()
	{
		$config = new SecurityConfiguration();
		$config->setCheckuser(self::ALLOW)
			->setCommunity(self::DENY)
			->setSuspended(self::DENY)
			->setDeclined(self::DENY)
			->setNew(self::DENY);

		return $config;
	}

	/**
	 * @param $value
	 * @param $filter
	 *
	 * @return bool
	 * @throws AccessDeniedException
	 * @category Security-Critical
	 */
	private function test($value, $filter)
	{
		if (!$filter) {
			return false;
		}

		if ($value == self::DENY) {
			// FILE_NOT_FOUND...?
			throw new AccessDeniedException();
		}

		return $value === self::ALLOW;
	}
}