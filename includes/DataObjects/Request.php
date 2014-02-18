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
    private $ipRequests = false;
    private $emailRequests = false;
    private $blacklistCache = null;
    
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
        return trim(getTrustedClientIP($this->ip, $this->forwardedip));
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
        
        $commentsQuery = $this->dbObject->prepare("SELECT COUNT(*) as num FROM comment where request = :id;");
        $commentsQuery->bindParam(":id", $this->id);
        
        $commentsQuery->execute();
        
        $this->hasComments = ($commentsQuery->fetchColumn() != 0);
        return $this->hasComments;
    }
    
    public function getRelatedEmailRequests()
    {
        if($this->emailRequests == false)
        {
            $query = $this->dbObject->prepare("SELECT * FROM request WHERE email = :email AND id != :id AND emailconfirm = 'Confirmed';");
            $query->bindParam(":id", $this->id);
            $query->bindParam(":email", $this->email);
            
            $query->execute();
            
            $this->emailRequests = $query->fetchAll(PDO::FETCH_CLASS, "Request");
            
            foreach($this->emailRequests as $r)
            {
                $r->setDatabase($this->dbObject);   
            }
        }
        
        return $this->emailRequests;
    }
        
    public function getRelatedIpRequests()
    {
        if($this->ipRequests == false)
        {
            $query = $this->dbObject->prepare("SELECT * FROM request WHERE (ip = :ip OR forwardedip LIKE :forwarded) AND id != :id AND emailconfirm = 'Confirmed';");
            
            $trustedIp = $this->getTrustedIp();
            $trustedFilter = '%' . $trustedIp . '%';
                        
            $query->bindParam(":id", $this->id);
            $query->bindParam(":ip", $trustedIp);
            $query->bindParam(":forwarded", $trustedFilter);
            
            $query->execute();
            
            $this->ipRequests = $query->fetchAll(PDO::FETCH_CLASS, "Request");
            
            foreach($this->emailRequests as $r)
            {
                $r->setDatabase($this->dbObject);   
            }
        }
        
        return $this->ipRequests;
    }
    
    public function isBlacklisted()
    {
        global $enableTitleBlacklist;
        
        if(! $enableTitleBlacklist || $this->blacklistCache === false)
        {
            return false;
        }
        
        $apiResult = file_get_contents("https://en.wikipedia.org/w/api.php?action=titleblacklist&tbtitle=" . urlencode($user) . "&tbaction=new-account&tbnooverride&format=php");
        
        $data = unserialize($apiResult);
        
        $result = $data['titleblacklist']['result'] == "ok";
        
        $this->blacklistCache = $result ? false : $data['titleblacklist']['line'];
        
        return $this->blacklistCache;
    }
    
    public function getComments()
    {
        return Comment::getForRequest($this->id, $this->dbObject);   
    }
}
