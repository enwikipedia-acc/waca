<?php

namespace Waca;

use \User;

/**
 * Class SecurityConfiguration
 * @package Waca
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

	/**
	 * @param string $admin
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public final function setAdmin($admin)
	{
		$this->admin = $admin;
		return $this;
	}

	/**
	 * @param string $user
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public final function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * @param string $checkuser
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public final function setCheckuser($checkuser)
	{
		$this->checkuser = $checkuser;
		return $this;
	}

	/**
	 * @param string $community
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public final function setCommunity($community)
	{
		$this->community = $community;
		return $this;
	}

	/**
	 * @param string $suspended
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public final function setSuspended($suspended)
	{
		$this->suspended = $suspended;
		return $this;
	}

	/**
	 * @param string $declined
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public final function setDeclined($declined)
	{
		$this->declined = $declined;
		return $this;
	}

	/**
	 * @param string $new
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	public final function setNew($new)
	{
		$this->new = $new;
		return $this;
	}

	/**
	 * @param User $user Tests if a user is allowed to perform an action
	 * @return bool
	 * @category Security-Critical
	 */
	public final function allows(User $user)
	{
		$allowed = false;

		// admin
		if ($user->isAdmin()) {
			if ($this->admin === self::DENY) {
				return false;
			}

			$allowed |= $this->admin === self::ALLOW;
		}
		// TODO: finish me off

		// user
		if ($user->isUser()) {
			if ($this->user === self::DENY) {
				return false;
			}

			$allowed |= $this->user === self::ALLOW;
		}

		// checkuser
		if ($user->isCheckuser()) {
			if ($this->checkuser === self::DENY) {
				return false;
			}

			$allowed |= $this->checkuser === self::ALLOW;
		}

		// community
		if ($user->isCommunityUser()) {
			if ($this->community === self::DENY) {
				return false;
			}

			$allowed |= $this->community === self::ALLOW;
		}

		// suspended
		if ($user->isSuspended()) {
			if ($this->suspended === self::DENY) {
				return false;
			}

			$allowed |= $this->suspended === self::ALLOW;
		}

		// declined
		if ($user->isDeclined()) {
			if ($this->declined === self::DENY) {
				return false;
			}

			$allowed |= $this->declined === self::ALLOW;
		}

		// new
		if ($user->isNew()) {
			if ($this->new === self::DENY) {
				return false;
			}

			$allowed |= $this->new === self::ALLOW;
		}

		return $allowed;
	}
}