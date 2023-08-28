<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use PDO;
use Waca\Helpers\OAuthUserHelper;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\Tasks\ConsoleTaskBase;

class ClearOAuthDataTask extends ConsoleTaskBase
{
    public function execute()
    {
        $database = $this->getDatabase();

        $users = UserSearchHelper::get($database)->inIds(
            $database->query('SELECT user FROM oauthtoken WHERE type = \'access\'')->fetchAll(PDO::FETCH_COLUMN)
        );

        foreach ($users as $u) {
            $oauth = new OAuthUserHelper($u, $database, $this->getOAuthProtocolHelper(), $this->getSiteConfiguration());
            $oauth->detach();
        }

        $database->exec('DELETE FROM oauthtoken');
        $database->exec('DELETE FROM oauthidentity');
    }
}