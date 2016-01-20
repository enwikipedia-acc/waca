<?php

/**
 * AntiSpoofCache data object
 */
class AntiSpoofCache extends DataObject
{
	protected $username;
	protected $data;
	protected $timestamp;

	public static function getByUsername($username, PdoDatabase $database)
	{
		$statement = $database->prepare(<<<SQL
SELECT *
FROM antispoofcache
WHERE username = :id AND timestamp > date_sub(now(), interval 3 hour)
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

	public function getUsername()
	{
		return $this->username;
	}

	public function setUsername($username)
	{
		$this->username = $username;
	}

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

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	public function save()
	{
		if ($this->isNew) {
			// insert
			// clear old data first
			$this->dbObject->exec("delete from antispoofcache where timestamp < date_sub(now(), interval 3 hour);");

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
