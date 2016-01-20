<?php

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
	 * @var int ID of the object
	 */
	protected $id = 0;

	/**
	 * @var bool
	 * TODO: we should probably make this a read-only method rather than public - why should anything external set this?
	 */
	public $isNew = true;

	/**
	 * @var PdoDatabase
	 */
	protected $dbObject;

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
	 * Retrieves a data object by it's row ID.
	 * @param int $id
	 * @param PdoDatabase $database
	 * @return DataObject|null
	 */
	public static function getById($id, PdoDatabase $database)
	{
		$statement = $database->prepare("SELECT * FROM `" . strtolower(get_called_class()) . "` WHERE id = :id LIMIT 1;");
		$statement->bindValue(":id", $id);

		$statement->execute();

		$resultObject = $statement->fetchObject(get_called_class());

		if ($resultObject != false) {
			$resultObject->isNew = false;
			$resultObject->setDatabase($database);
		}

		return $resultObject;
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
		$statement = $this->dbObject->prepare(
			"DELETE FROM `"
			. strtolower(get_called_class())
			. "` WHERE id = :id LIMIT 1;");

		$statement->bindValue(":id", $this->id);
		$statement->execute();

		$this->id = 0;
		$this->isNew = true;
	}
	
	public function getObjectDescription()
	{
		return '[' . get_called_class() . " " . $this->getId() . ']';	
	}
}
