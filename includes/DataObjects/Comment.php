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
        
        if(User::getCurrent()->isAdmin() || User::getCurrent()->isCheckuser())
        {
            // current user is an admin or checkuser, so retrieve everything.
            $statement = $database->prepare("SELECT * FROM comment WHERE request = :target;");
        }
        else
        {
            // current user isn't an admin, so limit to only those which are visibile to users, and private comments the user has posted themselves.
            $statement = $database->prepare("SELECT * FROM comment WHERE request = :target AND (visibility = 'user' || user = :userid);");
            $statement->bindParam(":userid", User::getCurrent()->getId());    
        }
        
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
            $statement = $this->dbObject->prepare("UPDATE comment SET comment = :comment, visibility = :visibility WHERE id = :id LIMIT 1;");
            $statement->bindParam(":id", $this->id);
            $statement->bindParam(":comment", $this->comment);
            $statement->bindParam(":visibility", $this->visibility);
            
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
