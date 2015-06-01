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
	 * @param int $objectid 
	 */
	public function setObjectId($objectid)
	{
		$this->objectid = $objectid;
	}

	public function getObjectType()
	{
		return $this->objecttype;
	}

	/**
	 * Summary of setObjectType
	 * @param string $objecttype 
	 */
	public function setObjectType($objecttype)
	{
		$this->objecttype = $objecttype;
	}

	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Summary of setUser
	 * @param int|User $user 
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
}
