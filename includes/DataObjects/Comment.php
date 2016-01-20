<?php

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
	 * @param integer $id
	 * @param null|PdoDatabase $database
	 * @return Comment[]
	 * @throws Exception
	 */
	public static function getForRequest($id, PdoDatabase $database = null)
	{
		if ($database == null) {
			$database = gGetDb();
		}

		if (User::getCurrent()->isAdmin() || User::getCurrent()->isCheckuser()) {
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
			$statement->bindValue(":userid", User::getCurrent()->getId());
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
SET comment = :comment, visibility = :visibility
WHERE id = :id LIMIT 1;
SQL
			);
			$statement->bindValue(":id", $this->id);
			$statement->bindValue(":comment", $this->comment);
			$statement->bindValue(":visibility", $this->visibility);

			if (!$statement->execute()) {
				throw new Exception($statement->errorInfo());
			}
		}
	}

	public function getTime()
	{
		return $this->time;
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

	public function setUser($user)
	{
		$this->user = $user;
	}

	public function getComment()
	{
		return $this->comment;
	}

	public function setComment($comment)
	{
		$this->comment = $comment;
	}

	public function getVisibility()
	{
		return $this->visibility;
	}

	public function setVisibility($visibility)
	{
		$this->visibility = $visibility;
	}

	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Summary of getRequestObject
	 * @return Request|null
	 */
	public function getRequestObject()
	{
		return Request::getById($this->request, $this->dbObject);
	}

	public function setRequest($request)
	{
		$this->request = $request;
	}
}
