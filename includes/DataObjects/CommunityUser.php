<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\DataObjects;

use DateTime;
use Waca\IIdentificationVerifier;

/**
 * User data object
 */
class CommunityUser extends User
{
    public function getId()
    {
        return -1;
    }

    public function save()
    {
        // Do nothing
    }

    #region properties

    /**
     * @return string
     */
    public function getUsername()
    {
        return '[Community]';
    }

    public function setUsername($username)
    {
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        global $cDataClearEmail;

        return $cDataClearEmail;
    }

    public function setEmail($email)
    {
    }

    public function getStatus()
    {
        return "Community";
    }

    public function getOnWikiName()
    {
        return "127.0.0.1";
    }

    public function setOnWikiName($onWikiName)
    {
    }

    public function getLastActive()
    {
        $now = new DateTime();

        return $now->format("Y-m-d H:i:s");
    }

    public function getForceLogout()
    {
        return true;
    }

    public function setForceLogout($forceLogout)
    {
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
    }

    public function getConfirmationDiff()
    {
        return null;
    }

    public function setConfirmationDiff($confirmationDiff)
    {
    }


    public function setUseAlternateSkin($useAlternate)
    {
    }

    #endregion

    #region user access checks

    public function isNewUser()
    {
        return false;
    }

    public function isDeactivated(): bool
    {
        return false;
    }

    public function isCommunityUser()
    {
        return true;
    }

    #endregion

    public function getApprovalDate()
    {
        $data = DateTime::createFromFormat("Y-m-d H:i:s", "1970-01-01 00:00:00");

        return $data;
    }
}
