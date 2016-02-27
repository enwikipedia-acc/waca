<?php
namespace Waca\DataObjects;

use Exception;
use PdoDatabase;
use Waca\DataObject;
use Waca\Environment;

/**
 * Interface data object
 *
 * Interface messages for those messages which are not templates.
 */
class InterfaceMessage extends DataObject
{
	/** @var string */
	private $content;
	/** @var int */
	private $updatecounter;
	/** @var string */
	private $description;
	/** @var string */
	private $type;
	/**
	 * The "site notice" interface message ID
	 */
	const SITENOTICE = '31';
	/**
	 * The "banned" interface message ID
	 */
	const DECL_BANNED = '19';

	/**
	 * Get a message.
	 *
	 * @param int         $key The ID to look up
	 *
	 * @param PdoDatabase $database
	 *
	 * @return string The content for display
	 */
	public static function get($key, PdoDatabase $database)
	{
		/** @var InterfaceMessage $message */
		$message = self::getById($key, $database);

		return $message->getContentForDisplay();
	}

	/**
	 * Saves the object
	 * @throws Exception
	 */
	public function save()
	{
		if ($this->isNew) {
			// insert
			$statement = $this->dbObject->prepare(<<<SQL
INSERT INTO interfacemessage (updatecounter, description, type, content)
VALUES (0, :desc, :type, :content);
SQL
			);
			$statement->bindValue(":type", $this->type);
			$statement->bindValue(":desc", $this->description);
			$statement->bindValue(":content", $this->content);
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
UPDATE interfacemessage
SET type = :type, description = :desc, content = :content, updatecounter = updatecounter + 1
WHERE id = :id
LIMIT 1;
SQL
			);
			$statement->bindValue(":id", $this->id);
			$statement->bindValue(":type", $this->type);
			$statement->bindValue(":desc", $this->description);
			$statement->bindValue(":content", $this->content);

			if (!$statement->execute()) {
				throw new Exception($statement->errorInfo());
			}
		}
	}

	/**
	 * Gets the content of the message
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Gets the content of the message intended for display.
	 * @return string
	 * @todo We should probably swap this for Smarty.
	 */
	public function getContentForDisplay()
	{
		global $baseurl;

		$message = $this->content;

		if (strpos($message, "%VERSION%") !== false) {
			$message = str_replace('%VERSION%', Environment::getToolVersion(), $message);
		}

		$message = str_replace('%TSURL%', $baseurl, $message);

		return $message;
	}

	/**
	 * Sets the content of the message
	 *
	 * @param string $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}

	/**
	 * Gets the message update counter
	 * @return int
	 */
	public function getUpdateCounter()
	{
		return $this->updatecounter;
	}

	/**
	 * Gets the description of the message
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Sets the description of the message
	 *
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * Gets the type of the message
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Sets the type of the message
	 *
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * Gets a user-visible description of the object.
	 * @return string
	 */
	public function getObjectDescription()
	{
		// @todo fixme
		$description = '<a href="acc.php?action=messagemgmt&amp;view=' . $this->getId() . '">'
			. htmlentities($this->description)
			. "</a>";

		return $description;
	}
}
