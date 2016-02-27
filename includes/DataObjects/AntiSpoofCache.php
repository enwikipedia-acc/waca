<?php
namespace Waca\DataObjects;

use Exception;
use PdoDatabase;
use Waca\DataObject;

/**
 * AntiSpoofCache data object
 */
class AntiSpoofCache extends DataObject
{
	/** @var string */
	protected $username;
	/** @var string */
	protected $data;
	/** @var string */
	protected $timestamp;

	public static function getByUsername($username, PdoDatabase $database)
	{
		$statement = $database->prepare(<<<SQL
SELECT *
FROM antispoofcache
WHERE username = :id AND timestamp > date_sub(now(), INTERVAL 3 HOUR)
LIMIT 1
SQL
		);
		$statement->bindValue(":id", $username);

		$statement->execute();

		$resultObject = $statement->fetchObject(get_called_class());

		if ($resultObject != false) {
			$resultObject->isNew = false;
			$resultObject->setDatabase($database);
		}

		return $resultObject;
	}

	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * @param string $username
	 */
	public function setUsername($username)
	{
		$this->username = $username;
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
	 * @return string
	 * @todo convert to timestamp?
	 */
	public function getTimestamp()
	{
		return $this->timestamp;
	}

	/**
	 * @throws Exception
	 */
	public function save()
	{
		if ($this->isNew) {
			// insert
			// clear old data first
			$this->dbObject->exec("DELETE FROM antispoofcache WHERE timestamp < date_sub(now(), INTERVAL 3 HOUR);");

			$statement = $this->dbObject->prepare("INSERT INTO antispoofcache (username, data) VALUES (:username, :data);");
			$statement->bindValue(":username", $this->username);
			$statement->bindValue(":data", $this->data);

			if ($statement->execute()) {
				$this->isNew = false;
				$this->id = $this->dbObject->lastInsertId();
			}
			else {
				throw new Exception($statement->errorInfo());
			}
		}
	}
}
