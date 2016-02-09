<?php

/**
 * Welcome template data object
 */
class WelcomeTemplate extends DataObject
{
	/** @var string */
	private $usercode;
	/** @var string */
	private $botcode;
	private $usageCache;

	/**
	 * Summary of getAll
	 *
	 * @param PdoDatabase $database
	 *
	 * @return WelcomeTemplate[]
	 */
	public static function getAll(PdoDatabase $database = null)
	{
		if ($database == null) {
			$database = gGetDb();
		}

		$statement = $database->prepare("SELECT * FROM welcometemplate;");

		$statement->execute();

		$result = array();
		/** @var WelcomeTemplate $v */
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
INSERT INTO welcometemplate (usercode, botcode) VALUES (:usercode, :botcode);
SQL
			);
			$statement->bindValue(":usercode", $this->usercode);
			$statement->bindValue(":botcode", $this->botcode);

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
UPDATE `welcometemplate` SET usercode = :usercode, botcode = :botcode WHERE id = :id LIMIT 1;
SQL
			);
			$statement->bindValue(":id", $this->id);
			$statement->bindValue(":usercode", $this->usercode);
			$statement->bindValue(":botcode", $this->botcode);

			if (!$statement->execute()) {
				throw new Exception($statement->errorInfo());
			}
		}
	}

	/**
	 * @return string
	 */
	public function getUserCode()
	{
		return $this->usercode;
	}

	/**
	 * @param string $usercode
	 */
	public function setUserCode($usercode)
	{
		$this->usercode = $usercode;
	}

	/**
	 * @return string
	 */
	public function getBotCode()
	{
		return $this->botcode;
	}

	/**
	 * @param string $botcode
	 */
	public function setBotCode($botcode)
	{
		$this->botcode = $botcode;
	}

	/**
	 * @return User[]
	 */
	public function getUsersUsingTemplate()
	{
		if ($this->usageCache === null) {
			$statement = $this->dbObject->prepare("SELECT * FROM user WHERE welcome_template = :id;");

			$statement->execute(array(":id" => $this->id));

			$result = array();
			/** @var WelcomeTemplate $v */
			foreach ($statement->fetchAll(PDO::FETCH_CLASS, 'User') as $v) {
				$v->isNew = false;
				$v->setDatabase($this->dbObject);
				$result[] = $v;
			}

			$this->usageCache = $result;
		}

		return $this->usageCache;
	}

	/**
	 * @return string
	 */
	public function getObjectDescription()
	{
		return '<a href="acc.php?action=templatemgmt&amp;view=' . $this->getId() . '">'
		. htmlentities($this->usercode) . "</a>";
	}
}
