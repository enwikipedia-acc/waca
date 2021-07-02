<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\DataObjects;

use Exception;
use Waca\DataObject;

class UserDomain extends DataObject
{
    /** @var int */
    private $user;

    /** @var int */
    private $domain;

    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO userdomain (
                    user, domain
                ) VALUES (
                    :user, :domain
                );
SQL
            );

            $statement->bindValue(":user", $this->user);
            $statement->bindValue(":domain", $this->domain);

            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }
        else {
            // insert / delete only, no updates please.
            throw new Exception('Updating domain membership is not available');
        }
    }

    /**
     * @return int
     */
    public function getUser(): int
    {
        return $this->user;
    }

    /**
     * @param int $user
     */
    public function setUser(int $user): void
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getDomain(): int
    {
        return $this->domain;
    }

    /**
     * @param int $domain
     */
    public function setDomain(int $domain): void
    {
        $this->domain = $domain;
    }


}