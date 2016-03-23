<?php
namespace Waca\DataObjects;

use Exception;
use Waca\DataObject;
use Waca\Environment;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;

/**
 * Interface data object
 *
 * Interface messages for those messages which are not templates.
 */
class InterfaceMessage extends DataObject
{
	/** @var string */
	private $content;
	/** @var string */
	private $description;
	/** @var string */
	private $type;
	/**
	 * The "site notice" interface message ID
	 */
	const SITENOTICE = '31';

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
SET type = :type, description = :desc, content = :content, updateversion = updateversion + 1
WHERE id = :id AND updateversion = :updateversion
LIMIT 1;
SQL
			);
			$statement->bindValue(':id', $this->id);
			$statement->bindValue(':updateversion', $this->updateversion);

			$statement->bindValue(':type', $this->type);
			$statement->bindValue(':desc', $this->description);
			$statement->bindValue(':content', $this->content);

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
}
