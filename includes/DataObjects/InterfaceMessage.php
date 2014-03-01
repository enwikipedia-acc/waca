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
        global $tsurl;
        
        $message = $this->content;
        
        if( strpos($message, "%VERSION%") !== false ) {
			$message = str_replace('%VERSION%', getToolVersion(), $message);
		}
		
		$message = str_replace('%TSURL%', $tsurl, $message);
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
