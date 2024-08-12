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
use Waca\Exceptions\ApplicationLogicException;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;

/**
 * Ban data object
 */
class RequestData extends DataObject
{
    const TYPE_IPV4 = 'ipv4';
    const TYPE_IPV6 = 'ipv6';
    const TYPE_EMAIL = 'email';
    const TYPE_USERAGENT = 'useragent';
    const TYPE_CLIENTHINT = 'clienthint';

    /** @var int */
    private $request;
    /** @var string */
    private $type;
    /** @var string|null */
    private $name;
    /** @var string */
    private $value;

    public static function getForRequest(int $requestId, PdoDatabase $database, ?string $type = null)
    {
        $statement = $database->prepare(<<<SQL
SELECT * FROM requestdata
WHERE request = :request AND type LIKE COALESCE(:type, '%');
SQL
        );

        $statement->bindValue(":request", $requestId);
        $statement->bindValue(":type", $type);

        $statement->execute();

        $result = array();
        /** @var RequestData $v */
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
INSERT INTO `requestdata` (request, type, name, value)
VALUES (:request, :type, :name, :value);
SQL
            );

            $statement->bindValue(":request", $this->request);
            $statement->bindValue(":type", $this->type);
            $statement->bindValue(":name", $this->name);
            $statement->bindValue(":value", $this->value);

            if ($statement->execute()) {
                $this->id = (int)$this->dbObject->lastInsertId();
            }
            else {
                throw new Exception($statement->errorInfo());
            }
        }
        else {
            // update
            throw new ApplicationLogicException('Updates to RequestData are not supported.');
        }
    }

    /**
     * @return int
     */
    public function getRequest(): int
    {
        return $this->request;
    }

    /**
     * @param int $request
     */
    public function setRequest(int $request): void
    {
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
