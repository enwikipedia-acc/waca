<?php

/**
 * Welcome Queue data object
 */
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
            $statement->bindValue(":user", $this->user);
            $statement->bindValue(":request", $this->request);
            $statement->bindValue(":status", $this->status);

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
            $statement->bindValue(":id", $this->id);
            $statement->bindValue(":status", $this->status);
            $statement->bindValue(":request", $this->request);
            $statement->bindValue(":user", $this->user);
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
