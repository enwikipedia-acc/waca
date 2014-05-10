<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

class InterfaceMessage extends DataObject
{
    private $content;
    private $updatecounter;
    private $description;
    private $type;
    
    const SITENOTICE = '31';
    const DECL_BLOCKED = '9';
    const DECL_BANNED = '19';
    const DECL_TAKEN = '10';
    const DECL_NUMONLY = '11';
    const DECL_EMAIL = '12';
    const DECL_INVCHAR = '13';
    const DECL_NONMATCHEMAIL = '27';
    const DECL_INVEMAIL = '14';
    const DECL_SULTAKEN = '28';
    const DECL_DUPEUSER = '17';
    const DECL_DUPEEMAIL = '18';
    const DECL_FINAL = '16';
    
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
        return self::getById($key, gGetDb())->getContentForDisplay();
    }
    
    public function save()
    {        
        if($this->isNew)
		{ // insert
			$statement = $this->dbObject->prepare("INSERT INTO interfacemessage (updatecounter, description, type, content) VALUES (0, :desc, :type, :content);");
			$statement->bindParam(":type", $this->type);
			$statement->bindParam(":desc", $this->description);
			$statement->bindParam(":content", $this->content);
			if($statement->execute())
			{
				$this->isNew = false;
				$this->id = $this->dbObject->lastInsertId();
			}
			else
			{
				throw new Exception($statement->errorInfo());
			}
		}
		else
		{ // update
			$statement = $this->dbObject->prepare("UPDATE interfacemessage SET type = :type, description = :desc, content = :content, updatecounter = updatecounter + 1 WHERE id = :id LIMIT 1;");
			$statement->bindParam(":id", $this->id);
			$statement->bindParam(":type", $this->type);
			$statement->bindParam(":desc", $this->description);
			$statement->bindParam(":content", $this->content);
            
			if(!$statement->execute())
			{
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
        
        if( strpos($message, "%VERSION%") !== false ) {
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
}
