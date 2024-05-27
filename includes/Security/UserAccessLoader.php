<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

use PDO;
use Waca\DataObjects\Domain;
use Waca\DataObjects\User;
use Waca\DataObjects\UserRole;

final class UserAccessLoader implements IUserAccessLoader
{
    public function loadRolesForUser(User $user): array
    {
        $domain = Domain::getCurrent($user->getDatabase());
        $userRoles = UserRole::getForUser($user->getId(), $user->getDatabase(), $domain->getId());

        return array_map(fn(UserRole $r): string => $r->getRole(), $userRoles);
    }

    public function loadDomainsForUser(User $user): array
    {
        $database = $user->getDatabase();

        $statement = $database->prepare(<<<'SQL'
            SELECT d.* 
            FROM domain d
            INNER JOIN userdomain ud on d.id = ud.domain
            WHERE ud.user = :user
            AND d.enabled = 1
SQL
        );
        $statement->execute([
            ':user' => $user->getId()
        ]);

        $resultObjects = $statement->fetchAll(PDO::FETCH_CLASS, Domain::class);

        /** @var Domain $t */
        foreach ($resultObjects as $t) {
            $t->setDatabase($database);
        }

        return $resultObjects;
    }
}