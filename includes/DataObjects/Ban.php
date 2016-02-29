<?php
namespace Waca\DataObjects;

use Exception;
use PDO;
use Waca\DataObject;
use Waca\PdoDatabase;

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
	 * Gets all active bans, filtered by the optional target.
	 *
	 * @param string|null $target
	 * @param PdoDatabase $database
	 *
	 * @return Ban[]
	 */
	public static function getActiveBans($target, PdoDatabase $database)
	{
		if ($target !== null) {
			$query = <<<SQL
SELECT * FROM ban WHERE target = :target AND (duration > UNIX_TIMESTAMP() OR duration = -1) AND active = 1;
SQL;
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
	 *
	 * @param     integer $id
	 * @param PdoDatabase $database
	 *
	 * @return Ban
	 */
	public static function getActiveId($id, PdoDatabase $database)
	{
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
	 *
	 * @param string      $target
	 * @param string      $type
	 * @param PdoDatabase $database
	 *
	 * @return Ban
	 */
	public static function getBanByTarget($target, $type, PdoDatabase $database)
	{
		$query = <<<SQL
SELECT * FROM ban
WHERE type = :type
	AND target = :target
	AND (duration > UNIX_TIMESTAMP() OR duration = -1)
	AND active = 1;
SQL;
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

	/**
	 * @throws Exception
	 */
	public function save()
	{
		if ($this->isNew) {
			// insert
			$statement = $this->dbObject->prepare(<<<SQL
INSERT INTO `ban` (type, target, user, reason, date, duration, active)
VALUES (:type, :target, :user, :reason, CURRENT_TIMESTAMP(), :duration, :active);
SQL
			);
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
			$statement = $this->dbObject->prepare(<<<SQL
UPDATE `ban`
SET duration = :duration, active = :active, user = :user
WHERE id = :id
LIMIT 1;
SQL
			);
			$statement->bindValue(":id", $this->id);
			$statement->bindValue(":duration", $this->duration);
			$statement->bindValue(":active", $this->active);
			$statement->bindValue(":user", $this->user);

			if (!$statement->execute()) {
				throw new Exception($statement->errorInfo());
			}
		}
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * @param string $target
	 */
	public function setTarget($target)
	{
		$this->target = $target;
	}

	/**
	 * @return string
	 */
	public function getReason()
	{
		return $this->reason;
	}

	/**
	 * @param string $reason
	 */
	public function setReason($reason)
	{
		$this->reason = $reason;
	}

	/**
	 * @return mixed
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @return mixed
	 */
	public function getDuration()
	{
		return $this->duration;
	}

	/**
	 * @param mixed $duration
	 */
	public function setDuration($duration)
	{
		$this->duration = $duration;
	}

	/**
	 * @return int
	 * @todo Boolean?
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @param int $active
	 */
	public function setActive($active)
	{
		$this->active = $active;
	}

	/**
	 * Gets a user-visible description of the object.
	 * @return string
	 */
	public function getObjectDescription()
	{
		return 'Ban #' . $this->getId() . " (" . htmlentities($this->target) . ")</a>";
	}

	/**
	 * @return int
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param int $user UserID of user who is setting the ban
	 *
	 * @throws Exception
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}
}
