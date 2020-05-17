<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
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