<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

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
     * Sets whether a checkuser is able to gain access.
     *
     * This is private because it's DANGEROUS. Checkusers are not mutually-exclusive with other rights. As such, a
     * suspended checkuser who tries to access a page which allows checkusers will be granted access to the page, UNLESS
     * that page is also set to DENY (note, not default) New/Declined/Suspended users. I have no problem with this
     * method being used, but please ONLY use it in this class in static methods of Security. Nowhere else.
     *
     * @param string $checkuser
     *
     * @return SecurityConfiguration
     * @category Security-Critical
     */
    public function setCheckuser($checkuser)
    {
        $this->checkuser = $checkuser;

        return $this;
    }

    /**
     * Returns if a user is required to be identified.
     *
     * @return boolean
     */
    public function requiresIdentifiedUser()
    {
        return $this->requireIdentified;
    }

    /**
     * @return string
     */
    public function getAdmin()
    {
        return $this->admin;
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
     * @return string
     */
    public function getUser()
    {
        return $this->user;
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
     * @return string
     */
    public function getCheckuser()
    {
        return $this->checkuser;
    }

    /**
     * @return string
     */
    public function getCommunity()
    {
        return $this->community;
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
     * @return string
     */
    public function getSuspended()
    {
        return $this->suspended;
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
     * @return string
     */
    public function getDeclined()
    {
        return $this->declined;
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
     * @return string
     */
    public function getNew()
    {
        return $this->new;
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
     * @param boolean $requireIdentified
     */
    public function setRequireIdentified($requireIdentified)
    {
        $this->requireIdentified = $requireIdentified;
    }
}