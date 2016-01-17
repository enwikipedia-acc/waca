<?php

/**
 * Interface data object
 *
 * Interface messages for those messages which are not templates.
 */
class InterfaceMessage extends DataObject
{
	private $content;
	private $updatecounter;
	private $description;
	private $type;

	const SITENOTICE = '31';
	const DECL_BANNED = '19';

	/**
	 * Get a message.
	 *
	 * This is going to be used as a new way of dealing with saved messages for #28
	 *
	 * The basic idea is there's a key stored in a new column, and we do lookups on that
	 * instead of a possibly variable auto-incrementing ID.
	 *
	 * We can use class constants so the keys are defined in one place only for now, and for
	 * now we are using the auto-incrementing ID as the value of the key, so this function
	 * just uses getById() at the moment.
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public static function get($key)
	{
		/** @var InterfaceMessage $message */
		$message = self::getById($key, gGetDb());
		return $message->getContentForDisplay();
	}

	public function save()
	{
		if ($this->isNew) {
// insert
			$statement = $this->dbObject->prepare("INSERT INTO interfacemessage (updatecounter, description, type, content) VALUES (0, :desc, :type, :content);");
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
			$statement = $this->dbObject->prepare("UPDATE interfacemessage SET type = :type, description = :desc, content = :content, updatecounter = updatecounter + 1 WHERE id = :id LIMIT 1;");
			$statement->bindValue(":id", $this->id);
			$statement->bindValue(":type", $this->type);
			$statement->bindValue(":desc", $this->description);
			$statement->bindValue(":content", $this->content);

			if (!$statement->execute()) {
				throw new Exception($statement->errorInfo());
			}
		}
	}

	public function getContent()
	{
		return $this->content;
	}

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

	public function setContent($content)
	{
		$this->content = $content;
	}

	public function getUpdateCounter()
	{
		return $this->updatecounter;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function setDescription($description)
	{
		$this->description = $description;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setType($type)
	{
		$this->type = $type;
	}
	
	public function getObjectDescription()
	{
		return '<a href="acc.php?action=messagemgmt&amp;view=' . $this->getId() . '">' . htmlentities($this->description) . "</a>";
	}
}
