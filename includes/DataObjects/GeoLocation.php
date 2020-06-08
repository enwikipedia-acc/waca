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
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;

/**
 * Geolocation data object
 *
 * This is a cache.
 */
class GeoLocation extends DataObject
{
    private $address;
    private $data;
    private $creation;

    /**
     * @param string      $address
     * @param PdoDatabase $database
     * @param bool        $forUpdate
     * @return GeoLocation
     */
    public static function getByAddress($address, PdoDatabase $database, $forUpdate = false)
    {
        $lockMode = $forUpdate ? ' FOR UPDATE' : '';
        $sql = "SELECT * FROM geolocation WHERE address = :id LIMIT 1" . $lockMode;

        $statement = $database->prepare($sql);
        $statement->bindValue(":id", $address);

        $statement->execute();

        $resultObject = $statement->fetchObject(get_called_class());

        if ($resultObject != false) {
            $resultObject->setDatabase($database);
        }

        return $resultObject;
    }

    public function save()
    {
        if ($this->isNew()) {
            // insert
            $statement = $this->dbObject->prepare("INSERT INTO `geolocation` (address, data) VALUES (:address, :data) ON DUPLICATE KEY UPDATE address = address;");

            $statement->bindValue(":address", $this->address);
            $statement->bindValue(":data", $this->data);

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
UPDATE `geolocation`
SET address = :address, data = :data, updateversion = updateversion + 1
WHERE id = :id AND updateversion = :updateversion;
SQL
            );

            $statement->bindValue(":id", $this->id);
            $statement->bindValue(":updateversion", $this->updateversion);

            $statement->bindValue(":address", $this->address);
            $statement->bindValue(":data", $this->data);

            if (!$statement->execute()) {
                throw new Exception($statement->errorInfo());
            }

            if ($statement->rowCount() !== 1) {
                throw new OptimisticLockFailedException();
            }

            $this->updateversion++;
        }
    }

    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return unserialize($this->data);
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = serialize($data);
    }

    /** @return DateTimeImmutable */
    public function getCreation()
    {
        return new DateTimeImmutable($this->creation);
    }
}
