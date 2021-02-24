<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Exception;
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
                try {
                    $database->beginTransaction();

                    $oauth = new OAuthUserHelper($u, $database, $this->getOAuthProtocolHelper(),
                        $this->getSiteConfiguration());

                    if ($oauth->getIdentity(true)->getAudience() !== $this->getSiteConfiguration()
                            ->getOAuthConsumerToken()) {
                        // not the current consumer token. Approval from the user is *required* for this.
                        printf("\n\nBoldly refusing to update OAuth data for user with legacy consumer: %s\n", $u->getUsername());
                        continue;
                    }

                    try {
                        $oauth->refreshIdentity();
                    }
                    catch (OAuthException $ex) {
                        $expiredStatement->execute(array(':u' => $u->getId()));
                    }

                    $database->commit();
                }
                catch (Exception $ex) {
                    $database->rollBack();

                    printf("\n\nFailed updating OAuth data for %s\n", $u->getUsername());
                    printf($ex->getMessage());
                }
                finally {
                    if ($database->hasActiveTransaction()) {
                        $database->rollBack();
                    }
                }
            }
        }

        $database->beginTransaction();
        $database->exec('DELETE FROM oauthtoken WHERE expiry IS NOT NULL AND expiry < NOW() AND type = \'request\'');
        $database->commit();
    }
}