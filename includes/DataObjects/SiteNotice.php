<?php
namespace Waca\DataObjects;

use Exception;
use Waca\DataObject;
use Waca\Exceptions\OptimisticLockFailedException;
use Waca\PdoDatabase;

/**
 * Interface data object
 *
 * Interface messages for those messages which are not templates.
 */
class SiteNotice extends DataObject
{
	/** @var string */
	private $content;

	/**
	 * Get a message.
	 *
	 * @param PdoDatabase $database
	 *
	 * @return string The content for display
	 */
	public static function get(PdoDatabase $database)
	{
		/** @var SiteNotice $message */
		$message = self::getById(1, $database);

		return $message->getContent();
	}

	/**
	 * Saves the object
	 * @throws Exception
	 */
	public function save()
	{
		if ($this->isNew()) {
			// insert
			throw new Exception('Not allowed to create new site notice object');
		}
		else {
			// update
			$statement = $this->dbObject->prepare(<<<SQL
UPDATE sitenotice
SET content = :content, updateversion = updateversion + 1
WHERE updateversion = :updateversion
LIMIT 1;
SQL
			);
			$statement->bindValue(':updateversion', $this->updateversion);

			$statement->bindValue(':content', $this->content);

			if (!$statement->execute()) {
				throw new Exception($statement->errorInfo());
			}

			if ($statement->rowCount() !== 1) {
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
	 * Sets the content of the message
	 *
	 * @param string $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}
}
