<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Background\Task;

use Waca\Background\CreationTaskBase;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Helpers\BotMediaWikiClient;
use Waca\Helpers\Interfaces\IMediaWikiClient;

class BotCreationTask extends CreationTaskBase
{
    /**
     * @return IMediaWikiClient
     */
    protected function getMediaWikiClient()
    {
        return new BotMediaWikiClient($this->getSiteConfiguration());
    }

    protected function getCreationReason(Request $request, User $user)
    {
        return parent::getCreationReason($request, $user) . ', on behalf of [[User:' . $user->getOnWikiName() . ']]';
    }
}