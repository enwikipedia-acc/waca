<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

/**
 * DataObject is the base class for all the database access classes. Each "DataObject" holds one record from the database, and
 * provides functions to allow loading from and saving to the database.
 *
 * Note: This requires the database tables to be named the same as the classes, and the database tables must have an "id" column.
 * Simple views can be used as a way of aliasing to allow for a transition period.
 * 
 * @author Simon Walker
 */
abstract class DataObject
{
    protected $isNew = true;
    
    protected $dbObject;
    
    public function setDatabase(PdoDatabase $db)
    {
        $this->dbObject = $db;
    }
    
	/**
     * Retrieves a data object by it's row ID.
     * @param $id
     */
	public static function getById($id, PdoDatabase $database) {
		$statement = $database->prepare("SELECT * FROM `" . strtolower( get_called_class() ) . "` WHERE id = :id LIMIT 1;");
		$statement->bindParam(":id", $id);

		$statement->execute();

		$resultObject = $statement->fetchObject( get_called_class() );

		if($resultObject != false)
		{
			$resultObject->isNew = false;
            $resultObject->setDatabase($database); 
		}

		return $resultObject;
	}

	/**
     * Saves a data object to the database, either updating or inserting a record.
     */
	public abstract function save();

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
		$statement = $this->dbObject->prepare("DELETE FROM `" . strtolower( get_called_class() ) . "` WHERE id = :id LIMIT 1;");
		$statement->bindParam(":id", $this->id);
		$statement->execute();

		$this->id=0;
		$this->isNew = true;
    }
}