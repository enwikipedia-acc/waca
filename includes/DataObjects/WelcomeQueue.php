<?php
if (!defined("ACC")) {
    die();
} // Invalid entry point

class WelcomeQueue extends DataObject
{
    private $user;
    private $request;
    private $status = "Open";
    
    public function save()
    {
        if($this->isNew)
		{ // insert
			$statement = $this->dbObject->prepare("INSERT INTO `welcomequeue` (user, request, status) VALUES (:user, :request, :status);");
			$statement->bindParam(":user", $this->user);
			$statement->bindParam(":request", $this->request);
			$statement->bindParam(":status", $this->status);
            
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
			$statement = $this->dbObject->prepare("UPDATE `welcomequeue` SET " . 
                "status = :status, user = :user, request = :request" .
                "WHERE id = :id LIMIT 1;");
			$statement->bindParam(":id", $this->id);
			$statement->bindParam(":status", $this->status);
			$statement->bindParam(":request", $this->request);
			$statement->bindParam(":user", $this->user);
			if(!$statement->execute())
			{
				throw new Exception($statement->errorInfo());
			}
		} 
        
    }
    
    public function getUser()
    {
		return $this->user;
	}

	public function setUser($user)
    {
		$this->user = $user;
	}

	public function getRequest()
    {
		return $this->request;
	}

	public function setRequest($request)
    {
		$this->request = $request;
	}

	public function getStatus()
    {
		return $this->status;
	}

	public function setStatus($status)
    {
		$this->status = $status;
	}
}
