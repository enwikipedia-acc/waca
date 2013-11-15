<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

class GeoLocation extends DataObject
{
    private $address;
    private $data;
    private $creation;
    
    public static function getByAddress($address, PdoDatabase $database)
    {
        $statement = $database->prepare("SELECT * FROM `" . strtolower( get_called_class() ) . "` WHERE address = :id LIMIT 1;");
		$statement->bindParam(":id", $address);

		$statement->execute();

		$resultObject = $statement->fetchObject( get_called_class() );

		if($resultObject != false)
		{
			$resultObject->isNew = false;
            $resultObject->setDatabase($database); 
		}

		return $resultObject;
    }
    
    public function save()
    {
		if($this->isNew)
		{ // insert
			$statement = $this->dbObject->prepare("INSERT INTO `geolocation` (address, data) VALUES (:address, :data);");
			$statement->bindParam(":address", $this->address);
			$statement->bindParam(":data", $this->data);
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
			$statement = $this->dbObject->prepare("UPDATE `geolocation` SET address = :address, data = :data WHERE id = :id LIMIT 1;");
			$statement->bindParam(":address", $this->address);
			$statement->bindParam(":id", $this->id);
			$statement->bindParam(":data", $this->data);
            
			if(!$statement->execute())
			{
				throw new Exception($statement->errorInfo());
			}
		} 
    }
    
    public function getAddress()
    {
        return $this->address;   
    }
    
    public function setAddress($address)
    {
        $this->address = $address;
    }
    
    public function getData()
    {
        return unserialize($this->data);
    }
    
    public function setData($data)
    {
        $this->data = serialize($data);
    }
    
    public function getCreation()
    {
        return $this->creation;   
    }
}
