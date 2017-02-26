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
use Waca\Exceptions\OptimisticLockFailedException;

class Credential extends DataObject
{
    /** @var int */
    private $user;
    /** @var int */
    private $factor;
    /** @var string */
    private $type;
    /** @var string */
    private $data;
    /** @var int */
    private $version;

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user;
    }

    /**
     * @param int $user
     */
    public function setUserId($user)
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * @param int $factor
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
INSERT INTO credential ( updateversion, user, factor, type, data, version )
VALUES ( 0, :user, :factor, :type, :data, :version );
SQL
            );
            $statement->bindValue(":user", $this->user);
            $statement->bindValue(":factor", $this->factor);
            $statement->bindValue(":type", $this->type);
            $statement->bindValue(":data", $this->data);
            $statement->bindValue(":version", $this->version);

            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }
        else {
            // update
            $statement = $this->dbObject->prepare(<<<SQL
                UPDATE credential
                SET   factor = :factor
                    , data = :data
                    , version = :version                 
                    , updateversion = updateversion + 1
                WHERE id = :id AND updateversion = :updateversion;
SQL
            );

            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':updateversion', $this->updateversion);

            $statement->bindValue(":factor", $this->factor);
            $statement->bindValue(":data", $this->data);
            $statement->bindValue(":version", $this->version);

            if (!$statement->execute()) {
                throw new Exception($statement->errorInfo());
            }

            if ($statement->rowCount() !== 1) {
                throw new OptimisticLockFailedException();
            }

            $this->updateversion++;
        }
    }
}