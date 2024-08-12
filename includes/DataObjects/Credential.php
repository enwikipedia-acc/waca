<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\DataObjects;

use DateTimeImmutable;
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
    private $timeout;
    /** @var int */
    private $disabled = 0;
    /** @var int */
    private $priority;

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

    /**
     * @return mixed
     */
    public function getTimeout()
    {
        if ($this->timeout === null) {
            return null;
        }

        return new DateTimeImmutable($this->timeout);
    }

    /**
     * @param mixed $timeout
     */
    public function setTimeout(DateTimeImmutable $timeout = null)
    {
        if ($timeout === null) {
            $this->timeout = null;
        }
        else {
            $this->timeout = $timeout->format('Y-m-d H:i:s');
        }
    }

    /**
     * @return int
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param int $disabled
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
INSERT INTO credential ( updateversion, user, factor, type, data, version, timeout, disabled, priority )
VALUES ( 0, :user, :factor, :type, :data, :version, :timeout, :disabled, :priority );
SQL
            );
            $statement->bindValue(":user", $this->user);
            $statement->bindValue(":factor", $this->factor);
            $statement->bindValue(":type", $this->type);
            $statement->bindValue(":data", $this->data);
            $statement->bindValue(":version", $this->version);
            $statement->bindValue(":timeout", $this->timeout);
            $statement->bindValue(":disabled", $this->disabled);
            $statement->bindValue(":priority", $this->priority);

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
                    , timeout = :timeout
                    , disabled = :disabled
                    , priority = :priority
                    , updateversion = updateversion + 1
                WHERE id = :id AND updateversion = :updateversion;
SQL
            );

            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':updateversion', $this->updateversion);

            $statement->bindValue(":factor", $this->factor);
            $statement->bindValue(":data", $this->data);
            $statement->bindValue(":version", $this->version);
            $statement->bindValue(":timeout", $this->timeout);
            $statement->bindValue(":disabled", $this->disabled);
            $statement->bindValue(":priority", $this->priority);

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