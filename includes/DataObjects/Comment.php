<?php
namespace Waca\DataObjects;

use Exception;
use PDO;
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;

/**
 * Comment data object
 */
class Comment extends DataObject
{
	private $time;
	private $user;
	private $comment;
	private $visibility = "user";
	private $request;

	/**
	 * @param integer     $id
	 * @param PdoDatabase $database
	 *
	 * @return Comment[]
	 * @throws Exception
	 */
	public static function getForRequest($id, PdoDatabase $database)
	{
		$currentUser = User::getCurrent($database);

		if ($currentUser->isAdmin() || $currentUser->isCheckuser()) {
			// current user is an admin or checkuser, so retrieve everything.
			$statement = $database->prepare("SELECT * FROM comment WHERE request = :target;");
		}
		else {
			// current user isn't an admin, so limit to only those which are visible to users, and private comments
			// the user has posted themselves.
			$statement = $database->prepare(<<<SQL
SELECT * FROM comment
WHERE request = :target AND (visibility = 'user' OR user = :userid);
SQL
			);
			$statement->bindValue(":userid", $currentUser->getId());
		}

		$statement->bindValue(":target", $id);

		$statement->execute();

		$result = array();
		/** @var Comment $v */
		foreach ($statement->fetchAll(PDO::FETCH_CLASS, get_called_class()) as $v) {
			$v->isNew = false;
			$v->setDatabase($database);
			$result[] = $v;
		}

		return $result;
	}

	/**
	 * @throws Exception
	 */
	public function save()
	{
		if ($this->isNew) {
			// insert
			$statement = $this->dbObject->prepare(<<<SQL
INSERT INTO comment ( time, user, comment, visibility, request )
VALUES ( CURRENT_TIMESTAMP(), :user, :comment, :visibility, :request );
SQL
			);
			$statement->bindValue(":user", $this->user);
			$statement->bindValue(":comment", $this->comment);
			$statement->bindValue(":visibility", $this->visibility);
			$statement->bindValue(":request", $this->request);

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
UPDATE comment
SET comment = :comment, visibility = :visibility, updateversion = updateversion + 1
WHERE id = :id AND updateversion = :updateversion
LIMIT 1;
SQL
			);

			$statement->bindValue(':id', $this->id);
			$statement->bindValue(':updateversion', $this->updateversion);

			$statement->bindValue(':comment', $this->comment);
			$statement->bindValue(':visibility', $this->visibility);

			if (!$statement->execute()) {
				throw new Exception($statement->errorInfo());
			}

			if($statement->rowCount() !== 1){
				throw new OptimisticLockFailedException();
			}

			$this->updateversion++;
		}
	}

	/**
	 * @return string
	 * @todo convert to date
	 */
	public function getTime()
	{
		return $this->time;
	}

	/**
	 * @return int
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param int $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * @return string
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @param string $comment
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
	}

	/**
	 * @return string
	 */
	public function getVisibility()
	{
		return $this->visibility;
	}

	/**
	 * @param string $visibility
	 */
	public function setVisibility($visibility)
	{
		$this->visibility = $visibility;
	}

	/**
	 * @return int
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @param int $request
	 */
	public function setRequest($request)
	{
		$this->request = $request;
	}
}
