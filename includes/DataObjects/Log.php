<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\DataObjects;

use DateTimeImmutable;
use Exception;
use Waca\DataObject;

/**
 * Class representing a log entry
 */
class Log extends DataObject
{
    /** @var int */
    private $objectid;
    /** @var string */
    private $objecttype;
    /** @var int */
    private $user;
    /** @var string */
    private $action;
    private $timestamp;
    /** @var string|null */
    private $comment;
    /** @var int */
    private $domain;

    /**
     * @throws Exception
     */
    public function save()
    {
        if ($this->isNew()) {
            $statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO log (objectid, objecttype, user, action, timestamp, comment, domain) 
                VALUES (:id, :type, :user, :action, CURRENT_TIMESTAMP(), :comment, :domain);
SQL
            );

            $statement->bindValue(":id", $this->objectid);
            $statement->bindValue(":type", $this->objecttype);
            $statement->bindValue(":user", $this->user);
            $statement->bindValue(":action", $this->action);
            $statement->bindValue(":comment", $this->comment);
            $statement->bindValue(":domain", $this->domain);

            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }
        else {
            throw new Exception("Updating logs is not available");
        }
    }

    /**
     * @throws Exception
     */
    public function delete()
    {
        throw new Exception("Deleting logs is not available.");
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectid;
    }

    /**
     * Summary of setObjectId
     *
     * @param int $objectId
     */
    public function setObjectId($objectId)
    {
        $this->objectid = $objectId;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objecttype;
    }

    /**
     * Summary of setObjectType
     *
     * @param string $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objecttype = $objectType;
    }

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Summary of setUser
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user->getId();
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Summary of setAction
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getTimestamp()
    {
        return new DateTimeImmutable($this->timestamp);
    }

    /**
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Summary of setComment
     *
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
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
