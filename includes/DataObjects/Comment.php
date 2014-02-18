<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

class Comment extends DataObject
{
    private $time;
    private $user;
    private $comment;
    private $visibility = "user";
    private $request;    
    
    public static function getForRequest($id, PdoDatabase $db = null)
    {
        if($database == null)
        {
            $database = gGetDb();   
        }
        
        $query = "SELECT * FROM comment WHERE request = :target;";
        $statement = $database->prepare($query);
        $statement->bindParam(":target", $id);
        
        $statement->execute();
        
        $result = array();
        foreach ($statement->fetchAll(PDO::FETCH_CLASS, get_called_class()) as $v)
        {
            $v->isNew = false;
            $v->setDatabase($database);
            $result[] = $v;
        }
        
        return $result;
    }
    
    public function save()
    {        
        if($this->isNew)
		{ // insert
            $statement = $this->dbObject->prepare("INSERT INTO comment ( time, user, comment, visibility, request ) VALUES ( CURRENT_TIMESTAMP(), :user, :comment, :visibility, :request );");
            $statement->bindParam(":user", $this->user);
            $statement->bindParam(":comment", $this->comment);
            $statement->bindParam(":visibility", $this->visibility);
            $statement->bindParam(":request", $this->request);
            
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
            $statement = $this->dbObject->prepare("UPDATE comment SET comment = :comment WHERE id = :id LIMIT 1;");
            $statement->bindParam(":id", $this->id);
            $statement->bindParam(":comment", $this->comment);
            
			if(!$statement->execute())
			{
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
    
	public function getRequestObject()
    {
		return Request::getById($this->request, $this->dbObject);
	}

	public function setRequest($request)
    {
		$this->request = $request;
	}
}
