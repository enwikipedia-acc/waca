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
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;

class UserPreference extends DataObject
{

    /** @var int */
    private $user;

    /** @var ?int */
    private $domain;

    /** @var string */
    private $preference;

    /** @var ?mixed */
    private $value;

    public static function getLocalPreference(PdoDatabase $database, int $user, string $preference, int $domain) {
        $statement = $database->prepare('SELECT * FROM userpreference WHERE preference = :preference AND USER = :user AND domain = :domain');
        $statement->execute([
            ':user' => $user,
            ':preference' => $preference,
            ':domain' => $domain
        ]);

        $resultObject = $statement->fetchObject(get_called_class());

        if ($resultObject !== false) {
            $resultObject->setDatabase($database);
        }

        return $resultObject;
    }

    public static function getGlobalPreference(PdoDatabase $database, int $user, string $preference) {
        $statement = $database->prepare('SELECT * FROM userpreference WHERE preference = :preference AND USER = :user AND domain IS NULL');
        $statement->execute([
            ':user' => $user,
            ':preference' => $preference
        ]);

        $resultObject = $statement->fetchObject(get_called_class());

        if ($resultObject !== false) {
            $resultObject->setDatabase($database);
        }

        return $resultObject;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function save()
    {
        if($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO `userpreference` (
                    user, domain, preference, value
                ) VALUES (
                    :user, :domain, :preference, :value
                );
SQL
            );
            $statement->bindValue(":user", $this->user);
            $statement->bindValue(":domain", $this->domain);
            $statement->bindValue(":preference", $this->preference);
            $statement->bindValue(":value", $this->value);

            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }else{
            // update
            $statement = $this->dbObject->prepare(<<<SQL
                UPDATE `userpreference` SET 
                    value = :value,
                    updateversion = updateversion + 1
                WHERE id = :id AND updateversion = :updateversion;
SQL
            );
            $statement->bindValue(":value", $this->value);

            $statement->bindValue(':id', $this->id);
            $statement->bindValue(':updateversion', $this->updateversion);

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
     * @return ?int
     */
    public function getDomain(): ?int
    {
        return $this->domain;
    }

    /**
     * @param ?int $domain
     */
    public function setDomain(?int $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getPreference(): string
    {
        return $this->preference;
    }

    /**
     * @param string $preference
     */
    public function setPreference(string $preference): void
    {
        $this->preference = $preference;
    }

    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed|null $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}
