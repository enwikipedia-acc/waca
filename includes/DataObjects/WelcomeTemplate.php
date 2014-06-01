<?php

/**
 * Welcome template data object
 */
class WelcomeTemplate extends DataObject
{
    private $usercode;
    private $botcode;
    
    public function save()
    {
        if($this->isNew)
		{ // insert
			$statement = $this->dbObject->prepare("INSERT INTO welcometemplate (usercode, botcode) VALUES (:usercode, :botcode);");
			$statement->bindValue(":usercode", $this->usercode);
			$statement->bindValue(":botcode", $this->botcode);
            
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
			$statement = $this->dbObject->prepare("UPDATE `welcometemplate` SET usercode = :usercode, botcode = :botcode WHERE id = :id LIMIT 1;");
			$statement->bindValue(":id", $this->id);
			$statement->bindValue(":usercode", $this->usercode);
			$statement->bindValue(":botcode", $this->botcode);
            
			if(!$statement->execute())
			{
				throw new Exception($statement->errorInfo());
			}
		}
    }
    
    public function getUserCode(){
        return $this->usercode;
    }

    public function setUserCode($usercode){
        $this->usercode = $usercode;
    }

    public function getBotCode(){
        return $this->botcode;
    }

    public function setBotCode($botcode){
        $this->botcode = $botcode;
    }
}
