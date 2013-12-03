<?php
if (!defined("ACC")) {
	die();
} // Invalid entry point

class Ban extends DataObject
{
    private $type;
    private $target;
    private $user;
    private $reason;
    private $date;
    private $duration;
    private $active;
    
    public static function getActiveBans($target = null, PdoDatabase $database = null)
    {
        if($database == null)
        {
            $database = gGetDb();   
        }
        
        if($target != null)
        {
            $query = "SELECT * FROM acc_ban WHERE ban_target = :target AND (ban_duration > UNIX_TIMESTAMP() OR ban_duration = -1) AND ban_active = 1;";
            $statement = $database->prepare($query);
            $statment->bindParam(":target", $target);
        }
        else
        {    
            $query = "SELECT * FROM acc_ban WHERE (ban_duration > UNIX_TIMESTAMP() OR ban_duration = -1) AND ban_active = 1;";
            $statement = $database->prepare($query);
        }
        
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }
    
    public function save()
    {
        if($this->isNew)
		{ // insert
			$statement = $this->dbObject->prepare("INSERT INTO `ban` (type, target, user, reason, date, duration, active) VALUES (:address, :data, :user, :reason, :date, :duration, :active);");
			$statement->bindParam(":type", $this->type);
			$statement->bindParam(":target", $this->target);
			$statement->bindParam(":user", $this->user);
			$statement->bindParam(":reason", $this->reason);
			$statement->bindParam(":date", $this->date);
			$statement->bindParam(":duration", $this->duration);
			$statement->bindParam(":active", $this->active);
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
			$statement = $this->dbObject->prepare("UPDATE `ban` SET type = :type, target = :target, user = :user, reason = :reason, date = :date, duration = :duration, active = :active WHERE id = :id LIMIT 1;");
			$statement->bindParam(":id", $this->id);
			$statement->bindParam(":type", $this->type);
			$statement->bindParam(":target", $this->target);
			$statement->bindParam(":user", $this->user);
			$statement->bindParam(":reason", $this->reason);
			$statement->bindParam(":date", $this->date);
			$statement->bindParam(":duration", $this->duration);
			$statement->bindParam(":active", $this->active);
            
			if(!$statement->execute())
			{
				throw new Exception($statement->errorInfo());
			}
		}         
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }
    
    public function getTarget()
    {
        return $this->target;
    }
    
    public function setTarget($target)
    {
        $this->target = $target;
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function setUser($user)
    {
        $this->user = $user;
    }
    
    public function getReason()
    {
        return $this->reason;
    }
    
    public function setReason($reason)
    {
        $this->reason = $reason;
    }
    
    public function getDate()
    {
        return $this->date;
    }
    
    public function setDate($date)
    {
        $this->date = $date;
    }
    
    public function getDuration()
    {
        return $this->duration;
    }
    
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }
    
    public function getActive()
    {
        return $this->active;
    }
    
    public function setActive($active)
    {
        $this->active = $active;
    }
}
