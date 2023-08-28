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
use Waca\DataObjects\Domain;
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
        // FIXME: domains!
        /** @var Domain $domain */
        $domain = Domain::getById(1, $this->getDatabase());

        return new BotMediaWikiClient($this->getSiteConfiguration(), $domain);
    }

    protected function getCreationReason(Request $request, User $user)
    {
        return parent::getCreationReason($request, $user) . ', on behalf of [[User:' . $user->getOnWikiName() . ']]';
    }
}