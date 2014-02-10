<?php
if (!defined("ACC")) {
    die();
} // Invalid entry point

class Request extends DataObject
{
    private $email;
    private $ip;
    private $name;
    private $comment;
    private $status;
    private $date;
    private $checksum;
    private $emailsent;
    private $emailconfirm;
    private $reserved;
    private $useragent;
    private $forwardedip;
    
    private $hasComments = "?";
    private $ipRequests = "-1"; // disabled for performance. set to ? to re-enable.
    private $emailRequests = "?";
    
    public function save()
    {
        if($this->isNew)
		{ // insert
			$statement = $this->dbObject->prepare(
                "INSERT INTO `request` (" . 
                "email, ip, name, comment, status, date, checksum, emailsent, emailconfirm, reserved, useragent, forwardedip" . 
                ") VALUES (" . 
                ":email, :ip, :name, :comment, :status, CURRENT_TIMESTAMP(), :checksum, :emailsent," . 
                ":emailconfirm, :reserved, :useragent, :forwardedip" . 
                ");");
			$statement->bindParam(":email", $this->email);
			$statement->bindParam(":ip", $this->ip);
			$statement->bindParam(":name", $this->name);
			$statement->bindParam(":comment", $this->comment);
			$statement->bindParam(":status", $this->status);
			$statement->bindParam(":checksum", $this->checksum);
			$statement->bindParam(":emailsent", $this->emailsent);
			$statement->bindParam(":emailconfirm", $this->emailconfirm);
			$statement->bindParam(":reserved", $this->reserved);
			$statement->bindParam(":useragent", $this->useragent);
			$statement->bindParam(":forwardedip", $this->forwardedip);
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
			$statement = $this->dbObject->prepare("UPDATE `request` SET " . 
                "status = :status, checksum = :checksum, emailsent = :emailsent, emailconfirm = :emailconfirm, " .
                "reserved = :reserved " .
                "WHERE id = :id LIMIT 1;");
			$statement->bindParam(":id", $this->id);
			$statement->bindParam(":status", $this->status);
			$statement->bindParam(":checksum", $this->checksum);
			$statement->bindParam(":emailsent", $this->emailsent);
			$statement->bindParam(":emailconfirm", $this->emailconfirm);
			$statement->bindParam(":reserved", $this->reserved);  
			if(!$statement->execute())
			{
				throw new Exception($statement->errorInfo());
			}
		} 
        
    }
    
    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getIp()
    {
        return $this->ip;
    }
    
    public function getTrustedIp()
    {
        return getTrustedClientIP($this->ip, $this->forwardedip);
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function setStatus($status)
    {
        $this->status = $status;   
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getChecksum()
    {
        return $this->checksum;
    }

    public function setChecksum($checksum)
    {
        $this->checksum = $checksum;
    }

    public function getEmailSent()
    {
        return $this->emailsent;
    }

    public function setEmailSent($emailsent)
    {
        $this->emailsent = $emailsent;
    }

    public function getEmailConfirm()
    {
        return $this->emailconfirm;
    }

    public function setEmailConfirm($emailconfirm)
    {
        $this->emailconfirm = $emailconfirm;
    }

    public function getReserved()
    {
        return $this->reserved;
    }
    
    public function getReservedObject()
    {
        return User::getById($this->reserved, $this->dbObject);   
    }

    public function setReserved($reserved)
    {
        $this->reserved = $reserved;
    }

    public function getUserAgent()
    {
        return $this->useragent;
    }

    public function setUserAgent($useragent)
    {
        $this->useragent = $useragent;
    }

    public function getForwardedIp()
    {
        return $this->forwardedip;
    }

    public function setForwardedIp($forwardedip)
    {
        $this->forwardedip = $forwardedip;
    }

    public function hasComments()
    {
        if($this->hasComments !== "?")
        {
            return $this->hasComments;   
        }
        
        if($this->comment != "")
        {
            $this->hasComments = true;
            return true;
        }
        
        $commentsQuery = $this->dbObject->prepare("SELECT COUNT(*) as num FROM acc_cmt where pend_id = :id;");
        $commentsQuery->bindParam(":id", $this->id);
        
        $commentsQuery->execute();
        
        $this->hasComments = ($commentsQuery->fetchColumn() == 0);
        return $this->hasComments;
    }
    
    public function numberOfIpRequests()
    {
        if($this->ipRequests !== "?")
        {
            return $this->ipRequests;   
        }
        
        $commentsQuery = $this->dbObject->prepare("SELECT COUNT(*) FROM request WHERE ip = :ip AND id != :id AND emailconfirm = 'Confirmed';");
        $commentsQuery->bindParam(":id", $this->id);
        $commentsQuery->bindParam(":ip", $this->ip);
        
        $commentsQuery->execute();
        
        $this->ipRequests = $commentsQuery->fetchColumn();
        return $this->ipRequests;
    }
    
    public function numberOfEmailRequests()
    {
        if($this->emailRequests !== "?")
        {
            return $this->emailRequests;   
        }
        
        $commentsQuery = $this->dbObject->prepare("SELECT COUNT(*) FROM request WHERE email = :email AND id != :id AND emailconfirm = 'Confirmed';");
        $commentsQuery->bindParam(":id", $this->id);
        $commentsQuery->bindParam(":email", $this->email);
        
        $commentsQuery->execute();
        
        $this->emailRequests = $commentsQuery->fetchColumn();
        
        return $this->emailRequests;
    }
}
