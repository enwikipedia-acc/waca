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
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;

/**
 * Ban data object
 */
class Ban extends DataObject
{
    const ACTION_BLOCK = 'block';
    const ACTION_DROP = 'drop';
    const ACTION_DEFER = 'defer';

    /** @var string|null */
    private $name;
    /** @var string|null */
    private $ip;
    /** @var int|null */
    private $ipmask;
    /** @var string|null */
    private $email;
    /** @var string|null */
    private $useragent;

    private $user;
    private $reason;
    private $date;
    private $duration;
    private $active;
    private $action = self::ACTION_BLOCK;
    private $actiontarget;
    private $visibility = 'user';

    /**
     * Gets all active bans, filtered by the optional target.
     *
     * @param PdoDatabase $database
     *
     * @return Ban[]
     */
    public static function getActiveBans(PdoDatabase $database)
    {
        $query = <<<SQL
SELECT * FROM ban 
WHERE (duration > UNIX_TIMESTAMP() OR duration is null) 
  AND active = 1;
SQL;
        $statement = $database->prepare($query);
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
     * Gets a ban by it's ID if it's currently active.
     *
     * @param     integer $id
     * @param PdoDatabase $database
     *
     * @return Ban
     */
    public static function getActiveId($id, PdoDatabase $database)
    {
        $statement = $database->prepare(<<<SQL
SELECT *
FROM ban
WHERE id = :id  AND (duration > UNIX_TIMESTAMP() OR duration is null) AND active = 1;
SQL
        );
        $statement->bindValue(":id", $id);

        $statement->execute();

        $resultObject = $statement->fetchObject(get_called_class());

        if ($resultObject != false) {
            $resultObject->setDatabase($database);
        }

        return $resultObject;
    }

    public static function getByIdList($values, PdoDatabase $database)
    {
        if (count($values) === 0) {
            return [];
        }

        // use the provided array to produce a list of question marks of the same length as the array.
        $valueCount = count($values);
        $inSection = str_repeat('?,', $valueCount - 1) . '?';

        // this is still parameterised! It's using positional parameters instead of named ones.
        $query = 'SELECT * FROM ban WHERE id IN (' . $inSection . ')';
        $statement = $database->prepare($query);

        // execute the statement with the provided parameter list.
        $statement->execute($values);

        $result = [];
        foreach ($statement->fetchAll(PDO::FETCH_CLASS, get_called_class()) as $v) {
            $v->setDatabase($database);
            $result[] = $v;
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
INSERT INTO `ban` (name, email, ip, ipmask, useragent, user, reason, date, duration, active, action, actiontarget, visibility)
VALUES (:name, :email, :ip, :ipmask, :useragent, :user, :reason, CURRENT_TIMESTAMP(), :duration, :active, :action, :actionTarget, :visibility);
SQL
            );

            $statement->bindValue(":name", $this->name);
            $statement->bindValue(":email", $this->email);
            $statement->bindValue(":ip", $this->ip);
            $statement->bindValue(":ipmask", $this->ipmask);
            $statement->bindValue(":useragent", $this->useragent);

            $statement->bindValue(":user", $this->user);
            $statement->bindValue(":reason", $this->reason);
            $statement->bindValue(":duration", $this->duration);
            $statement->bindValue(":active", $this->active);
            $statement->bindValue(":action", $this->action);
            $statement->bindValue(":actionTarget", $this->actiontarget);
            $statement->bindValue(":visibility", $this->visibility);

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
UPDATE `ban`
SET duration = :duration, active = :active, user = :user, action = :action, actiontarget = :actionTarget, 
    visibility = :visibility, updateversion = updateversion + 1
WHERE id = :id AND updateversion = :updateversion;
SQL
            );
            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':updateversion', $this->updateversion);

            $statement->bindValue(':duration', $this->duration);
            $statement->bindValue(':active', $this->active);
            $statement->bindValue(':user', $this->user);
            $statement->bindValue(":action", $this->action);
            $statement->bindValue(":actionTarget", $this->actiontarget);
            $statement->bindValue(":visibility", $this->visibility);

            if (!$statement->execute()) {
                throw new Exception($statement->errorInfo());
            }

            if ($statement->rowCount() !== 1) {
                throw new OptimisticLockFailedException();
            }

            $this->updateversion++;
        }
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active == 1;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active ? 1 : 0;
    }

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $user UserID of user who is setting the ban
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return string|null
     */
    public function getActionTarget()
    {
        return $this->actiontarget;
    }

    /**
     * @param string|null $actionTarget
     */
    public function setActionTarget($actionTarget): void
    {
        $this->actiontarget = $actionTarget;
    }

    /**
     * @return string
     */
    public function getVisibility() : string
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     */
    public function setVisibility(string $visibility): void
    {
        $this->visibility = $visibility;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getIp(): ?string
    {
        if ($this->ip === null) {
            return null;
        }

        return inet_ntop($this->ip);
    }

    /**
     * @return int|null
     */
    public function getIpMask(): ?int
    {
        return $this->ipmask;
    }

    /**
     * @param string|null $ip
     * @param int|null    $mask
     */
    public function setIp(?string $ip, ?int $mask): void
    {
        if($ip === null) {
            $this->ip = null;
        } else {
            $this->ip = inet_pton($ip);
        }

        $this->ipmask = $mask;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getUseragent(): ?string
    {
        return $this->useragent;
    }

    /**
     * @param string|null $useragent
     */
    public function setUseragent(?string $useragent): void
    {
        $this->useragent = $useragent;
    }
}
