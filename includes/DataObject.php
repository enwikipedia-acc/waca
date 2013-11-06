<?php

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client.

/**
 * DataObject is the base class for all the database access classes. Each "DataObject" holds one record from the database, and
 * provides functions to allow loading from and saving to the database.
 *
 * @author Simon Walker
 */
abstract class DataObject
{
    protected $isNew = true;
    
	/**
     * Retrieves a data object by it's row ID.
     * @param $id
     */
	public abstract static function getById($id);

	/**
     * Saves a data object to the database, either updating or inserting a record.
     */
	public abstract function save();

	/**
	 * Retrieves the ID attribute
	 */
	public abstract function getId();

	/**
	 * Deletes the object from the database
	 */
	public abstract function delete();
}