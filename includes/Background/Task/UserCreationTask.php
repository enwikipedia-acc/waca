<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Background\Task;

use Waca\Background\CreationTaskBase;
use Waca\Helpers\Interfaces\IMediaWikiClient;
use Waca\Helpers\OAuthUserHelper;

class UserCreationTask extends CreationTaskBase
{
    /**
     * @return IMediaWikiClient
     */
    protected function getMediaWikiClient()
    {
        $oauth = new OAuthUserHelper($this->getTriggerUser(), $this->getDatabase(), $this->getOauthProtocolHelper(),
            $this->getSiteConfiguration());

        return $oauth;
    }
}