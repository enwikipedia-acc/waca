<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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
    private ?int $domain;

    /**
     * @param int         $user
     * @param PdoDatabase $database
     * @param int         $domain
     *
     * @return UserRole[]
     */
    public static function getForUser(int $user, PdoDatabase $database, int $domain)
    {
        $sql = 'SELECT * FROM userrole WHERE user = :user AND (domain IS NULL OR domain = :domain)';
        $statement = $database->prepare($sql);
        $statement->bindValue(':user', $user);
        $statement->bindValue(':domain', $domain);

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
            $statement = $this->dbObject->prepare('INSERT INTO `userrole` (user, role, domain) VALUES (:user, :role, :domain);'
            );
            $statement->bindValue(":user", $this->user);
            $statement->bindValue(":role", $this->role);
            $statement->bindValue(":domain", $this->domain);

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

    public function getDomain(): ?int
    {
        return $this->domain;
    }

    public function setDomain(?int $domain): void
    {
        $this->domain = $domain;
    }
}
