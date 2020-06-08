<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\DataObjects;

use Exception;
use PDO;
use Waca\DataObject;
use Waca\PdoDatabase;

class UserRole extends DataObject
{
    /** @var int */
    private $user;
    /** @var string */
    private $role;

    /**
     * @param int         $userId
     * @param PdoDatabase $database
     *
     * @return UserRole[]
     */
    public static function getForUser($userId, PdoDatabase $database)
    {
        $sql = 'SELECT * FROM userrole WHERE user = :user';
        $statement = $database->prepare($sql);
        $statement->bindValue(':user', $userId);

        $statement->execute();

        $result = array();

        /** @var Ban $v */
        foreach ($statement->fetchAll(PDO::FETCH_CLASS, get_called_class()) as $v) {
            $v->setDatabase($database);
            $result[] = $v;
        }

        return $result;
    }

    /**
     * Saves a data object to the database, either updating or inserting a record.
     *
     * @throws Exception
     */
    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare('INSERT INTO `userrole` (user, role) VALUES (:user, :role);'
            );
            $statement->bindValue(":user", $this->user);
            $statement->bindValue(":role", $this->role);

            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }
        else {
            // update
            throw new Exception('Updating roles is not available');
        }
    }

    #region Properties

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }
    #endregion
}
