<?php

/**
 * Log short summary.
 *
 * Log description.
 *
 * @version 1.0
 * @author stwalkerster
 */
class Log extends DataObject
{
	private $objectid;
	private $objecttype;
	private $user;
	private $action;
	private $timestamp;
	private $comment;

	public function save()
	{
		if ($this->isNew) {
			$statement = $this->dbObject->prepare(<<<SQL
                INSERT INTO log (objectid, objecttype, user, action, timestamp, comment) 
                VALUES (:id, :type, :user, :action, CURRENT_TIMESTAMP(), :comment);
SQL
			);

			$statement->bindValue(":id", $this->objectid);
			$statement->bindValue(":type", $this->objecttype);
			$statement->bindValue(":user", $this->user);
			$statement->bindValue(":action", $this->action);
			$statement->bindValue(":comment", $this->comment);

			if ($statement->execute()) {
				$this->isNew = false;
				$this->id = $this->dbObject->lastInsertId();
			}
			else {
				throw new Exception($statement->errorInfo());
			}
		}
		else {
			throw new Exception("Updating logs is not available");
		}
	}

	public function delete()
	{
		throw new Exception("Deleting logs is not available.");
	}

	public function getObjectId()
	{
		return $this->objectid;
	}

	/**
	 * Summary of setObjectId
	 * @param int $objectId
	 */
	public function setObjectId($objectId)
	{
		$this->objectid = $objectId;
	}

	public function getObjectType()
	{
		return $this->objecttype;
	}

	/**
	 * Summary of setObjectType
	 * @param string $objectType
	 */
	public function setObjectType($objectType)
	{
		$this->objecttype = $objectType;
	}

	public function getUser()
	{
		return $this->user;
	}
	
	/**
	 * Summary of getUserObject
	 * @return User|null
	 */
	public function getUserObject()
	{
		return User::getById($this->user, $this->dbObject);
	}

	/**
	 * Summary of setUser
	 * @param User $user
	 */
	public function setUser($user)
	{
		if (is_a($user, "User")) {
			$this->user = $user->getId();   
		}
		else {
			$this->user = $user;   
		}
	}

	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Summary of setAction
	 * @param string $action 
	 */
	public function setAction($action)
	{
		$this->action = $action;
	}

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * Summary of setComment
	 * @param string $comment 
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
	}
	
	/**
	 * Let's be really sneaky here, and fake this to the object description of the logged object.
	 * @return string
	 */
	public function getObjectDescription()
	{
		$type = $this->objecttype;
		
		if ($type == "") {
			return "";
		}

		/** @var DataObject $object */
		$object = $type::getById($this->objectid, $this->dbObject);

		if ($object === false) {
			return '[' . $this->objecttype . " " . $this->objectid . ']';
		}
		
		return $object->getObjectDescription();
	}
}
