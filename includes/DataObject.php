<?php

namespace Waca;

use Waca\Exceptions\OptimisticLockFailedException;

/**
 * DataObject is the base class for all the database access classes. Each
 * "DataObject" holds one record from the database, and provides functions to
 * allow loading from and saving to the database.
 *
 * Note: This requires the database tables to be named the same as the classes,
 * and the database tables must have an "id" column. Simple views can be used
 * as a way of aliasing to allow for a transition period.
 *
 * @author Simon Walker
 */
abstract class DataObject
{
	/**
	 * @var bool
	 * @todo we should probably make this a read-only method rather than public - why should anything external set this?
	 */
	public $isNew = true;
	/** @var int ID of the object */
	protected $id = 0;
	/** @var int update version for optimistic locking */
	protected $updateversion = 0;
	/**
	 * @var PdoDatabase
	 */
	protected $dbObject;

	/**
	 * Retrieves a data object by it's row ID.
	 *
	 * @param int         $id
	 * @param PdoDatabase $database
	 *
	 * @return DataObject|false
	 */
	public static function getById($id, PdoDatabase $database)
	{
		$array = explode('\\', get_called_class());
		$realClassName = strtolower(end($array));

		$statement = $database->prepare("SELECT * FROM {$realClassName} WHERE id = :id LIMIT 1;");
		$statement->bindValue(":id", $id);

		$statement->execute();

		$resultObject = $statement->fetchObject(get_called_class());

		if ($resultObject != false) {
			$resultObject->isNew = false;
			$resultObject->setDatabase($database);
		}

		return $resultObject;
	}

	public function setDatabase(PdoDatabase $db)
	{
		$this->dbObject = $db;
	}

	/**
	 * Gets the database associated with this data object.
	 * @return PdoDatabase
	 */
	public function getDatabase()
	{
		return $this->dbObject;
	}

	/**
	 * Saves a data object to the database, either updating or inserting a record.
	 */
	abstract public function save();

	/**
	 * Retrieves the ID attribute
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Deletes the object from the database
	 */
	public function delete()
	{
		if($this->isNew) {
			// wtf?
			return;
		}

		$array = explode('\\', get_called_class());
		$realClassName = strtolower(end($array));

		$deleteQuery = "DELETE FROM {$realClassName} WHERE id = :id AND updateversion = :updateversion LIMIT 1;";
		$statement = $this->dbObject->prepare($deleteQuery);

		$statement->bindValue(":id", $this->id);
		$statement->bindValue(":updateversion", $this->updateversion);
		$statement->execute();

		if ($statement->rowCount() !== 1) {
			throw new OptimisticLockFailedException();
		}

		$this->id = 0;
		$this->isNew = true;
	}

	/**
	 * @return int
	 */
	public function getUpdateVersion()
	{
		return $this->updateversion;
	}

	/**
	 * Sets the update version.
	 *
	 * You should never call this to change the value of the update version. You should only call it when passing user
	 * input through.
	 *
	 * @param int $updateVersion
	 */
	public function setUpdateVersion($updateVersion)
	{
		$this->updateversion = $updateVersion;
	}
}
