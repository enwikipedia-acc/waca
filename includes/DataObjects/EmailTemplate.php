<?php
namespace Waca\DataObjects;

use Exception;
use PDO;
use PdoDatabase;
use Waca\DataObject;

/**
 * Email template data object
 *
 * This is the close reasons thing.
 */
class EmailTemplate extends DataObject
{
	const CREATED = "created";
	const NOT_CREATED = "not created";
	const NONE = null;
	private $name;
	private $text;
	private $jsquestion;
	private $active = 1;
	private $preloadonly = 0;
	private $defaultaction = self::NOT_CREATED;

	/**
	 * Gets active non-preload templates
	 *
	 * @param string      $defaultAction Default action to take (EmailTemplate::CREATED or EmailTemplate::NOT_CREATED)
	 * @param PdoDatabase $database
	 *
	 * @return array|false
	 */
	public static function getActiveTemplates($defaultAction, PdoDatabase $database)
	{
		global $createdid;

		$statement = $database->prepare(<<<SQL
SELECT * FROM `emailtemplate`
WHERE defaultaction = :forcreated AND active = 1 AND preloadonly = 0 AND id != :createdid;
SQL
		);
		$statement->bindValue(":createdid", $createdid);
		$statement->bindValue(":forcreated", $defaultAction);

		$statement->execute();

		$resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

		/** @var EmailTemplate $t */
		foreach ($resultObject as $t) {
			$t->setDatabase($database);
			$t->isNew = false;
		}

		return $resultObject;
	}

	/**
	 * Gets active non-preload and preload templates, optionally filtered by the default action.
	 *
	 * @param null|bool|string $defaultAction Default action to take (EmailTemplate::CREATED,
	 *                                        EmailTemplate::NOT_CREATED, or EmailTemplate::NONE), or optionally null to
	 *                                        just get everything.
	 * @param PdoDatabase      $database
	 *
	 * @return array|false
	 */
	public static function getAllActiveTemplates($defaultAction, PdoDatabase $database)
	{
		$statement = $database->prepare("SELECT * FROM `emailtemplate` WHERE defaultaction = :forcreated AND active = 1;");

		if ($defaultAction === false) {
			$statement = $database->prepare(
				"SELECT * FROM `emailtemplate` WHERE defaultaction NOT IN ('created', 'not created') AND active = 1;");
		}

		if ($defaultAction === null) {
			$statement = $database->prepare("SELECT * FROM `emailtemplate` WHERE  active = 1;");
		}

		$statement->bindValue(":forcreated", $defaultAction);

		$statement->execute();

		$resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

		/** @var EmailTemplate $t */
		foreach ($resultObject as $t) {
			$t->setDatabase($database);
			$t->isNew = false;
		}

		return $resultObject;
	}

	/**
	 * Gets all the unactive templates
	 *
	 * @param PdoDatabase $database
	 *
	 * @return array
	 */
	public static function getAllInactiveTemplates(PdoDatabase $database)
	{
		$statement = $database->prepare("SELECT * FROM `emailtemplate` WHERE  active = 0;");
		$statement->execute();

		$resultObject = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

		/** @var EmailTemplate $t */
		foreach ($resultObject as $t) {
			$t->setDatabase($database);
			$t->isNew = false;
		}

		return $resultObject;
	}

	/**
	 * @param string      $name
	 * @param PdoDatabase $database
	 *
	 * @return EmailTemplate|false
	 */
	public static function getByName($name, PdoDatabase $database)
	{
		$statement = $database->prepare("SELECT * FROM `emailtemplate` WHERE name = :name LIMIT 1;");
		$statement->bindValue(":name", $name);

		$statement->execute();

		$resultObject = $statement->fetchObject(get_called_class());

		if ($resultObject != false) {
			$resultObject->isNew = false;
			$resultObject->setDatabase($database);
		}

		return $resultObject;
	}

	/**
	 * @return EmailTemplate
	 */
	public static function getDroppedTemplate()
	{
		$t = new EmailTemplate();
		$t->id = 0;
		$t->active = 1;
		$t->name = 'Dropped';

		return $t;
	}

	/**
	 * @throws Exception
	 */
	public function save()
	{
		if ($this->isNew) {
			// insert
			$statement = $this->dbObject->prepare(<<<SQL
INSERT INTO `emailtemplate` (name, text, jsquestion, defaultaction, active, preloadonly)
VALUES (:name, :text, :jsquestion, :defaultaction, :active, :preloadonly);
SQL
			);
			$statement->bindValue(":name", $this->name);
			$statement->bindValue(":text", $this->text);
			$statement->bindValue(":jsquestion", $this->jsquestion);
			$statement->bindValue(":defaultaction", $this->defaultaction);
			$statement->bindValue(":active", $this->active);
			$statement->bindValue(":preloadonly", $this->preloadonly);

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
UPDATE `emailtemplate`
SET name = :name,
	text = :text,
	jsquestion = :jsquestion,
	defaultaction = :defaultaction,
	active = :active,
	preloadonly = :preloadonly
WHERE id = :id LIMIT 1;
SQL
			);
			$statement->bindValue(":id", $this->id);
			$statement->bindValue(":name", $this->name);
			$statement->bindValue(":text", $this->text);
			$statement->bindValue(":jsquestion", $this->jsquestion);
			$statement->bindValue(":defaultaction", $this->defaultaction);
			$statement->bindValue(":active", $this->active);
			$statement->bindValue(":preloadonly", $this->preloadonly);

			if (!$statement->execute()) {
				throw new Exception($statement->errorInfo());
			}
		}
	}

	/**
	 * Override delete() from DataObject
	 */
	public function delete()
	{
		throw new Exception("You shouldn't be doing that, you'll break logs.");
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @param string $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}

	/**
	 * @return string|null
	 */
	public function getJsquestion()
	{
		return $this->jsquestion;
	}

	/**
	 * @param string $jsquestion
	 */
	public function setJsquestion($jsquestion)
	{
		$this->jsquestion = $jsquestion;
	}

	/**
	 * @return string
	 */
	public function getDefaultAction()
	{
		return $this->defaultaction;
	}

	/**
	 * @param string $defaultAction
	 */
	public function setDefaultAction($defaultAction)
	{
		$this->defaultaction = $defaultAction;
	}

	/**
	 * @return bool
	 */
	public function getActive()
	{
		return $this->active == 1;
	}

	/**
	 * @param bool $active
	 */
	public function setActive($active)
	{
		$this->active = $active ? 1 : 0;
	}

	/**
	 * @return bool
	 */
	public function getPreloadOnly()
	{
		return $this->preloadonly == 1;
	}

	/**
	 * @param bool $preloadonly
	 */
	public function setPreloadOnly($preloadonly)
	{
		$this->preloadonly = $preloadonly ? 1 : 0;
	}

	/**
	 * @return string
	 */
	public function getObjectDescription()
	{
		global $baseurl;
		$safeName = htmlentities($this->name);
		$id = $this->id;

		return "<a href=\"{$baseurl}/internal.php/emailManagement/view?id={$id}\">Email Template #{$id} ({$safeName})</a>";
	}
}
