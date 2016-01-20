<?php

/**
 * Ban data object
 */
class Ban extends DataObject
{
	private $type;
	private $target;
	private $user;
	private $reason;
	private $date;
	private $duration;
	private $active;

	/**
	 * Gets all bans, expired and active filtered by the optional target.
	 * @param $target string The email, IP, or name of the target of the ban
	 * @param PdoDatabase $database gGetDb()
	 * @return Ban[]
	 */
	public static function getAllBans($target = null, PdoDatabase $database = null)
	{
		if ($database == null) {
			$database = gGetDb();
		}

		if ($target != null) {
			$query = "SELECT * FROM ban WHERE target = :target;";
			$statement = $database->prepare($query);
			$statement->bindValue(":target", $target);
		}
		else {
			$query = "SELECT * FROM ban;";
			$statement = $database->prepare($query);
		}

		$statement->execute();

		$result = array();
		/** @var Ban $v */
		foreach ($statement->fetchAll(PDO::FETCH_CLASS, get_called_class()) as $v) {
			$v->isNew = false;
			$v->setDatabase($database);
			$result[] = $v;
		}

		return $result;
	}

	/**
	 * Gets all active bans, filtered by the optional target.
	 * @param $target
	 * @param PdoDatabase $database
	 * @return Ban[]
	 */
	public static function getActiveBans($target = null, PdoDatabase $database = null)
	{
		if ($database == null) {
			$database = gGetDb();
		}

		if ($target != null) {
			$query = "SELECT * FROM ban WHERE target = :target AND (duration > UNIX_TIMESTAMP() OR duration = -1) AND active = 1;";
			$statement = $database->prepare($query);
			$statement->bindValue(":target", $target);
		}
		else {
			$query = "SELECT * FROM ban WHERE (duration > UNIX_TIMESTAMP() OR duration = -1) AND active = 1;";
			$statement = $database->prepare($query);
		}

		$statement->execute();

		$result = array();

		/** @var Ban $v */
		foreach ($statement->fetchAll(PDO::FETCH_CLASS, get_called_class()) as $v) {
			$v->isNew = false;
			$v->setDatabase($database);
			$result[] = $v;
		}

		return $result;
	}

	/**
	 * Gets a ban by it's ID if it's currently active.
	 * @param $id
	 * @param PdoDatabase $database
	 * @return Ban
	 */
	public static function getActiveId($id, PdoDatabase $database = null)
	{
		if ($database == null) {
			$database = gGetDb();
		}

		$statement = $database->prepare(<<<SQL
SELECT *
FROM ban
WHERE id = :id  AND (duration > UNIX_TIMESTAMP() OR duration = -1) AND active = 1;
SQL
		);
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
	 * Get all active bans for a target and type.
	 * @param string $target
	 * @param string $type
	 * @param PdoDatabase $database
	 * @return Ban
	 */
	public static function getBanByTarget($target, $type, PdoDatabase $database = null)
	{
		if ($database == null) {
			$database = gGetDb();
		}

		$query = "SELECT * FROM ban WHERE type = :type AND target = :target AND (duration > UNIX_TIMESTAMP() OR duration = -1) AND active = 1;";
		$statement = $database->prepare($query);
		$statement->bindValue(":target", $target);
		$statement->bindValue(":type", $type);

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
			$statement = $this->dbObject->prepare("INSERT INTO `ban` (type, target, user, reason, date, duration, active) VALUES (:type, :target, :user, :reason, CURRENT_TIMESTAMP(), :duration, :active);");
			$statement->bindValue(":type", $this->type);
			$statement->bindValue(":target", $this->target);
			$statement->bindValue(":user", $this->user);
			$statement->bindValue(":reason", $this->reason);
			$statement->bindValue(":duration", $this->duration);
			$statement->bindValue(":active", $this->active);
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
			$statement = $this->dbObject->prepare("UPDATE `ban` SET duration = :duration, active = :active, user = :user WHERE id = :id LIMIT 1;");
			$statement->bindValue(":id", $this->id);
			$statement->bindValue(":duration", $this->duration);
			$statement->bindValue(":active", $this->active);
			$statement->bindValue(":user", $this->user);

			if (!$statement->execute()) {
				throw new Exception($statement->errorInfo());
			}
		}
	}

	public function getType()
	{
		return $this->type;
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function getTarget()
	{
		return $this->target;
	}

	public function setTarget($target)
	{
		$this->target = $target;
	}

	public function getUser()
	{
		$user = User::getById($this->user, gGetDb());

		return $user;
	}

	public function setUser($user)
	{
		if (User::getById($user, gGetDb()) == false) {
			$u = User::getByUsername($user, gGetDb());
			if ($u == false) {
				throw new Exception("Unknown user trying to create ban!");
			}

			$this->user = $u->getId();
		}
		else {
			$this->user = $user;
		}
	}

	public function getReason()
	{
		return $this->reason;
	}

	public function setReason($reason)
	{
		$this->reason = $reason;
	}

	public function getDate()
	{
		return $this->date;
	}

	public function getDuration()
	{
		return $this->duration;
	}

	public function setDuration($duration)
	{
		$this->duration = $duration;
	}

	public function getActive()
	{
		return $this->active;
	}

	public function setActive($active)
	{
		$this->active = $active;
	}
	
	public function getObjectDescription()
	{
		return 'Ban #' . $this->getId() . " (" . htmlentities($this->target) . ")</a>";
	}
}
