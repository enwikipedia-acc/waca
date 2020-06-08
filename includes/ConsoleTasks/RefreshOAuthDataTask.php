<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use PDO;
use Waca\DataObjects\User;
use Waca\Exceptions\OAuthException;
use Waca\Helpers\OAuthUserHelper;
use Waca\Helpers\SearchHelpers\UserSearchHelper;
use Waca\Tasks\ConsoleTaskBase;

class RefreshOAuthDataTask extends ConsoleTaskBase
{
    public function execute()
    {
        $database = $this->getDatabase();

        $idList = $database
            ->query('SELECT user FROM oauthtoken WHERE type = \'access\' AND expiry IS NULL')
            ->fetchAll(PDO::FETCH_COLUMN);

        if (count($idList) > 0) {
            /** @var User[] $users */
            $users = UserSearchHelper::get($database)->inIds($idList)->fetch();

            $expiredStatement = $database
                ->prepare('UPDATE oauthtoken SET expiry = CURRENT_TIMESTAMP() WHERE user = :u AND type = \'access\'');

            foreach ($users as $u) {
                $oauth = new OAuthUserHelper($u, $database, $this->getOAuthProtocolHelper(),
                    $this->getSiteConfiguration());

                try {
                    $oauth->refreshIdentity();
                }
                catch (OAuthException $ex) {
                    $expiredStatement->execute(array(':u' => $u->getId()));
                }
            }
        }

        $this->getDatabase()
            ->exec('DELETE FROM oauthtoken WHERE expiry IS NOT NULL AND expiry < NOW() AND type = \'request\'');
    }
}