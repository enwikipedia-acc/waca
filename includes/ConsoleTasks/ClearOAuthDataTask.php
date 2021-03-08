<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Waca\Helpers\OAuthUserHelper;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\Tasks\ConsoleTaskBase;

class ClearOAuthDataTask extends ConsoleTaskBase
{
    public function execute()
    {
        $database = $this->getDatabase();

        $users = UserSearchHelper::get($database)->inIds(
            $database->query('SELECT user FROM oauthtoken WHERE type = \'access\'')->fetchColumn()
        );

        foreach ($users as $u) {
            $oauth = new OAuthUserHelper($u, $database, $this->getOAuthProtocolHelper(), $this->getSiteConfiguration());
            $oauth->detach();
        }

        $database->exec('DELETE FROM oauthtoken');
        $database->exec('DELETE FROM oauthidentity');
    }
}