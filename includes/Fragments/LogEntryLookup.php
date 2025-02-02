<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Fragments;

use Waca\DataObjects\Log;
use Waca\DataObjects\User;
use Waca\Helpers\SearchHelpers\LogSearchHelper;
use Waca\PdoDatabase;

trait LogEntryLookup
{
    protected function getLogEntry(string $action, User $user, PdoDatabase $database): ?string
    {
        /** @var Log[] $logs */
        $logs = LogSearchHelper::get($database, null)
            ->byAction($action)
            ->byObjectType('User')
            ->byObjectId($user->getId())
            ->limit(1)
            ->fetch();

        if (count($logs) > 0) {
            return $logs[0]->getComment();
        }

        return null;
    }
}