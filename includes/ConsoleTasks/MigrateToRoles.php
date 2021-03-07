<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use PDO;
use Waca\DataObjects\Log;
use Waca\DataObjects\User;
use Waca\DataObjects\UserRole;
use Waca\Tasks\ConsoleTaskBase;

class MigrateToRoles extends ConsoleTaskBase
{
    public function execute()
    {
        $communityUser = User::getCommunity();

        $database = $this->getDatabase();
        $statement = $database->query('SELECT id, status, checkuser FROM user;');
        $update = $database->prepare("UPDATE user SET status = 'Active' WHERE id = :id;");

        $users = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as $user) {
            $toAdd = array('user');

            if ($user['status'] === 'Admin') {
                $toAdd[] = 'admin';
            }

            if ($user['checkuser'] == 1) {
                $toAdd[] = 'checkuser';
            }

            foreach ($toAdd as $x) {
                $a = new UserRole();
                $a->setUser($user['id']);
                $a->setRole($x);
                $a->setDatabase($database);
                $a->save();
            }

            $logData = serialize(array(
                'added' => $toAdd,
                'removed' => array(),
                'reason' => 'Initial migration'
            ));

            $log = new Log();
            $log->setDatabase($database);
            $log->setAction('RoleChange');
            $log->setObjectId($user['id']);
            $log->setObjectType('User');
            $log->setUser($communityUser);
            $log->setComment($logData);
            $log->save();

            if ($user['status'] === 'Admin' || $user['status'] === 'User') {
                $update->execute(array('id' => $user['id']));
            }
        }

        $database->exec("UPDATE schemaversion SET version = 25;");
    }
}
