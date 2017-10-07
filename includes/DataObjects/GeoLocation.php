<?php

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
	 * @param string $address
	 * @param PdoDatabase $database
	 * @param bool $forUpdate
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
			$resultObject->isNew = false;
			$resultObject->setDatabase($database);
		}

		return $resultObject;
	}

	public function save()
	{
		if ($this->isNew) {
// insert
			$statement = $this->dbObject->prepare("INSERT INTO `geolocation` (address, data) VALUES (:address, :data) ON DUPLICATE KEY UPDATE address = address;");
			$statement->bindValue(":address", $this->address);
			$statement->bindValue(":data", $this->data);
			if ($statement->execute()) {
				$this->isNew = false;
				$this->id = $this->dbObject->lastInsertId();
			}
			else {
				throw new Exception($statement->errorInfo());
			}
		}
		else {
// update
			$statement = $this->dbObject->prepare("UPDATE `geolocation` SET address = :address, data = :data WHERE id = :id LIMIT 1;");
			$statement->bindValue(":address", $this->address);
			$statement->bindValue(":id", $this->id);
			$statement->bindValue(":data", $this->data);

			if (!$statement->execute()) {
				throw new Exception($statement->errorInfo());
			}
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

	public function getData()
	{
		return unserialize($this->data);
	}

	public function setData($data)
	{
		$this->data = serialize($data);
	}

	public function getCreation()
	{
		return $this->creation;
	}
}
